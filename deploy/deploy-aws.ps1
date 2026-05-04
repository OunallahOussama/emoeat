<# 
  EmoEat - Full AWS EC2 Deployment Script
  =========================================
  Prerequisites:
    - AWS CLI installed: https://aws.amazon.com/cli/
    - AWS CLI configured: run "aws configure" with your Access Key
  
  Usage: .\deploy-aws.ps1
#>

param(
    [string]$KeyName = "emoeat-key",
    [string]$InstanceType = "t2.micro",
    [string]$Region = "eu-west-1",
    [string]$ProjectPath = (Get-Location).Path
)

$ErrorActionPreference = "Stop"

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  EmoEat - AWS EC2 Deployment" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# --- Step 1: Check AWS CLI ---
Write-Host "[1/7] Checking AWS CLI..." -ForegroundColor Yellow
try { aws --version | Out-Null } catch {
    Write-Host "ERROR: AWS CLI not installed. Install from https://aws.amazon.com/cli/" -ForegroundColor Red
    exit 1
}

# --- Step 2: Create Key Pair ---
Write-Host "[2/7] Creating key pair '$KeyName'..." -ForegroundColor Yellow
$KeyPath = "$env:USERPROFILE\.ssh\$KeyName.pem"
if (!(Test-Path $KeyPath)) {
    New-Item -ItemType Directory -Path "$env:USERPROFILE\.ssh" -Force | Out-Null
    aws ec2 create-key-pair --key-name $KeyName --region $Region --query "KeyMaterial" --output text | Out-File -Encoding ASCII $KeyPath
    icacls $KeyPath /inheritance:r /grant:r "${env:USERNAME}:(R)" | Out-Null
    Write-Host "  Key saved to $KeyPath" -ForegroundColor Green
} else {
    Write-Host "  Key already exists at $KeyPath" -ForegroundColor Green
}

# --- Step 3: Create Security Group ---
Write-Host "[3/7] Creating security group..." -ForegroundColor Yellow
$VpcId = aws ec2 describe-vpcs --region $Region --filters "Name=isDefault,Values=true" --query "Vpcs[0].VpcId" --output text
$SgExists = aws ec2 describe-security-groups --region $Region --filters "Name=group-name,Values=emoeat-sg" --query "SecurityGroups[0].GroupId" --output text 2>$null

if ($SgExists -eq "None" -or [string]::IsNullOrEmpty($SgExists)) {
    $SgId = aws ec2 create-security-group --group-name "emoeat-sg" --description "EmoEat App Security Group" --vpc-id $VpcId --region $Region --query "GroupId" --output text
    
    # SSH
    aws ec2 authorize-security-group-ingress --group-id $SgId --protocol tcp --port 22 --cidr "0.0.0.0/0" --region $Region | Out-Null
    # HTTP
    aws ec2 authorize-security-group-ingress --group-id $SgId --protocol tcp --port 80 --cidr "0.0.0.0/0" --region $Region | Out-Null
    # phpMyAdmin
    aws ec2 authorize-security-group-ingress --group-id $SgId --protocol tcp --port 8081 --cidr "0.0.0.0/0" --region $Region | Out-Null
    # HTTPS
    aws ec2 authorize-security-group-ingress --group-id $SgId --protocol tcp --port 443 --cidr "0.0.0.0/0" --region $Region | Out-Null
    
    Write-Host "  Security group created: $SgId" -ForegroundColor Green
} else {
    $SgId = $SgExists
    Write-Host "  Security group already exists: $SgId" -ForegroundColor Green
}

# --- Step 4: Get Ubuntu 24.04 AMI ---
Write-Host "[4/7] Finding Ubuntu 24.04 AMI..." -ForegroundColor Yellow
$AmiId = aws ec2 describe-images --region $Region --owners 099720109477 `
    --filters "Name=name,Values=ubuntu/images/hvm-ssd-gp3/ubuntu-noble-24.04-amd64-server-*" "Name=state,Values=available" `
    --query "Images | sort_by(@, &CreationDate) | [-1].ImageId" --output text

Write-Host "  AMI: $AmiId" -ForegroundColor Green

# --- Step 5: Launch EC2 Instance ---
Write-Host "[5/7] Launching EC2 instance ($InstanceType)..." -ForegroundColor Yellow
$InstanceId = aws ec2 run-instances `
    --region $Region `
    --image-id $AmiId `
    --instance-type $InstanceType `
    --key-name $KeyName `
    --security-group-ids $SgId `
    --tag-specifications "ResourceType=instance,Tags=[{Key=Name,Value=emoeat-server}]" `
    --query "Instances[0].InstanceId" --output text

Write-Host "  Instance launched: $InstanceId" -ForegroundColor Green
Write-Host "  Waiting for instance to be running..." -ForegroundColor Yellow
aws ec2 wait instance-running --instance-ids $InstanceId --region $Region

# Get public IP
$PublicIp = aws ec2 describe-instances --instance-ids $InstanceId --region $Region `
    --query "Reservations[0].Instances[0].PublicIpAddress" --output text

Write-Host "  Public IP: $PublicIp" -ForegroundColor Green

# --- Step 6: Wait for SSH to be ready ---
Write-Host "[6/7] Waiting for SSH to be ready (~30s)..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# --- Step 7: Upload project and run setup ---
Write-Host "[7/7] Uploading project and deploying..." -ForegroundColor Yellow

# Upload project files
scp -i $KeyPath -o StrictHostKeyChecking=no -r `
    "$ProjectPath/Dockerfile" `
    "$ProjectPath/docker-compose.yml" `
    "$ProjectPath/.dockerignore" `
    "$ProjectPath/config" `
    "$ProjectPath/images" `
    "$ProjectPath/docker" `
    "$ProjectPath/*.php" `
    "$ProjectPath/*.css" `
    "ubuntu@${PublicIp}:~/"

# Move files into proper structure and run setup
ssh -i $KeyPath -o StrictHostKeyChecking=no "ubuntu@$PublicIp" @"
mkdir -p ~/emoeat/config ~/emoeat/images ~/emoeat/docker
mv ~/Dockerfile ~/docker-compose.yml ~/.dockerignore ~/emoeat/ 2>/dev/null || true
mv ~/config/* ~/emoeat/config/ 2>/dev/null || true
mv ~/images/* ~/emoeat/images/ 2>/dev/null || true
mv ~/docker/* ~/emoeat/docker/ 2>/dev/null || true
mv ~/*.php ~/*.css ~/emoeat/ 2>/dev/null || true
sudo apt update -qq && sudo apt install -y -qq docker.io docker-compose-v2
sudo systemctl enable docker && sudo systemctl start docker
sudo usermod -aG docker ubuntu
sudo chmod 666 /var/run/docker.sock
cd ~/emoeat && docker compose up -d --build
"@

Write-Host ""
Write-Host "=========================================" -ForegroundColor Green
Write-Host "  DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  App:        http://$PublicIp" -ForegroundColor White
Write-Host "  phpMyAdmin: http://${PublicIp}:8081" -ForegroundColor White
Write-Host ""
Write-Host "  SSH:  ssh -i $KeyPath ubuntu@$PublicIp" -ForegroundColor White
Write-Host "  Admin login: admin@emoeat.com / password" -ForegroundColor White
Write-Host "=========================================" -ForegroundColor Green
