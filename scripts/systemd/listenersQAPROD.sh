#!/bin/bash

#you need to change the cmd line arguments for the installer listener depending on the machine runnning it
cat << EOF > "/etc/systemd/system/installerListener.service"
[Unit]
Description= Starts deploy listener
After=network.target

[Service]
Type=simple
User=root
ExecStart=/bin/php /home/vboxuser/git/rabbitmqphp_example/deployment/install/installerListener.php QA rabbit
WorkingDirectory= /home/vboxuser/git/rabbitmqphp_example/deployment/install 
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

chmod 644 /etc/systemd/system/installerListener.service

systemctl daemon-reload

systemctl enable installerListener.service

systemctl restart installerListener.service

systemctl status installerListener.service


