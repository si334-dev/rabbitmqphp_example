#!/bin/bash

cat << EOF > "/etc/systemd/system/deployListener.service"
[Unit]
Description= Starts deploy listener
After=network.target

[Service]
Type=simple
User=root
ExecStart=/bin/php /home/vboxuser/rabbitmqphp_example/deployment/deployListener.php
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

chmod 644 /etc/systemd/system/deployListener.service

systemctl daemon-reload

systemctl enable deployListener.service

systemctl restart deployListener.service

systemctl status deployListener.service


