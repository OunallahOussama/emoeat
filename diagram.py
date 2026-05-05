from diagrams import Diagram, Cluster, Edge
from diagrams.aws.compute import EC2
from diagrams.aws.network import Route53, VPCElasticNetworkInterface
from diagrams.aws.engagement import SimpleEmailServiceSes
from diagrams.onprem.network import Nginx
from diagrams.onprem.container import Docker
from diagrams.onprem.database import MySQL
from diagrams.onprem.client import Users
from diagrams.programming.language import PHP

# Architecture Diagram
with Diagram("EmoEat - Architecture EC2", show=False, filename="emoeat_architecture", direction="LR"):
    
    users = Users("Utilisateurs\nemoeat.health")
    dns = Route53("DNS\nNamecheap\nA Record")
    eip = VPCElasticNetworkInterface("Elastic IP\n44.212.102.37")

    with Cluster("AWS EC2 - t3.small\ni-036038631083b7d67"):
        
        with Cluster("Docker Compose"):
            nginx = Nginx("Nginx\n(SSL/Reverse Proxy)\nPort 80, 443")
            certbot = Docker("Certbot\n(Let's Encrypt)")
            
            with Cluster("Application Layer"):
                php = PHP("PHP 8.2 + Apache\n(emoeat-php)\nPort 80")
                phpmyadmin = PHP("phpMyAdmin\n(emoeat-phpmyadmin)\nPort 8081")
            
            with Cluster("Database Layer"):
                mysql = MySQL("MySQL 8.0\n(emoeat-mysql)\nPort 3306")

            with Cluster("Email Layer"):
                mailpit = Docker("Mailpit\n(SMTP Relay + UI)\nPort 1025/8025")

    ses = SimpleEmailServiceSes("AWS SES\nno-reply@emoeat.health")

    # Connections
    users >> Edge(label="HTTPS") >> dns
    dns >> eip
    eip >> nginx
    nginx >> Edge(label="proxy_pass :80") >> php
    nginx >> Edge(label="proxy_pass :8081") >> phpmyadmin
    php >> Edge(label="PDO MySQL") >> mysql
    php >> Edge(label="msmtp :1025") >> mailpit
    phpmyadmin >> Edge(label="PMA_HOST") >> mysql
    certbot >> Edge(label="SSL certs", style="dashed") >> nginx
    mailpit >> Edge(label="SMTP relay :587") >> ses


# App Flow Diagram
with Diagram("EmoEat - Application Flow", show=False, filename="emoeat_app_flow", direction="TB"):
    
    user = Users("Utilisateur")
    
    with Cluster("Authentication"):
        login = PHP("login.php")
        register = PHP("register.php")
        forgot = PHP("forgot_password.php")
        reset = PHP("reset_password.php")
    
    with Cluster("User Dashboard"):
        dashboard = PHP("dashboard.php")
        reco = PHP("recommandation.php")
        historique = PHP("historique.php")
        profile = PHP("profile.php")
    
    with Cluster("Admin Panel"):
        admin_dash = PHP("dashboard_admin.php")
        admin_users = PHP("admin_users.php")
        admin_emotions = PHP("admin_emotions.php")
        admin_foods = PHP("admin_foods.php")
        admin_log = PHP("admin_activity_log.php")
    
    with Cluster("Services"):
        db = MySQL("MySQL 8.0\nemoeat DB")
        email = Docker("Mailpit → SES\nno-reply@emoeat.health")

    # User flow
    user >> Edge(label="Auth") >> login
    user >> Edge(label="Register") >> register
    register >> Edge(label="Welcome email") >> email
    login >> dashboard
    dashboard >> reco
    dashboard >> historique
    dashboard >> profile
    
    # Password reset flow
    user >> Edge(label="Forgot?") >> forgot
    forgot >> Edge(label="Reset link email") >> email
    forgot >> reset
    reset >> login
    
    # Admin flow
    login >> Edge(label="role=admin") >> admin_dash
    admin_dash >> admin_users
    admin_dash >> admin_emotions
    admin_dash >> admin_foods
    admin_dash >> admin_log
    
    # DB connections
    reco >> db
    historique >> db
    profile >> db
    admin_users >> db
    admin_emotions >> db
    admin_foods >> db
    register >> db
    login >> db
