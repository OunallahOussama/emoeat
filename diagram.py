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


# App Flow Diagram (MVC)
with Diagram("EmoEat - Application Flow (MVC)", show=False, filename="emoeat_app_flow", direction="TB"):
    
    user = Users("Utilisateur")
    
    with Cluster("Front Controller"):
        router = PHP("public/index.php\nRouter")
    
    with Cluster("Controllers"):
        auth_ctrl = PHP("AuthController\nlogin, register\nforgot/reset password")
        dash_ctrl = PHP("DashboardController\nuser stats")
        reco_ctrl = PHP("RecommendationController\nemotion → food")
        hist_ctrl = PHP("HistoryController\npast recommendations")
        profile_ctrl = PHP("ProfileController\nweight, height, goals")
        admin_ctrl = PHP("AdminController\nusers, foods, emotions\nactivity log")
    
    with Cluster("Models"):
        models = MySQL("User, Food, Emotion\nRecommendation, UserProfile\nUserEmotion, ActivityLog\nPasswordResetToken")

    with Cluster("Services"):
        db = MySQL("MySQL 8.0\nemoeat DB")
        email = Docker("Mailpit → SES\nno-reply@emoeat.health")

    # Request flow
    user >> Edge(label="HTTP Request") >> router
    router >> auth_ctrl
    router >> dash_ctrl
    router >> reco_ctrl
    router >> hist_ctrl
    router >> profile_ctrl
    router >> Edge(label="role=ADMIN") >> admin_ctrl
    
    # Email flows
    auth_ctrl >> Edge(label="Welcome/Reset email") >> email
    
    # All controllers → Models → DB
    auth_ctrl >> models
    dash_ctrl >> models
    reco_ctrl >> models
    hist_ctrl >> models
    profile_ctrl >> models
    admin_ctrl >> models
    models >> db
