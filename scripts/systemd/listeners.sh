#!/bin/bash

cat << EOF > "/etc/systemd/system/dmzListener.service"
[Unit]
Description= Starts dmz listener
After=network.target

[Service]
Type=simple
User=root
ExecStart=/bin/php /home/vboxuser/git/rabbitmqphp_example/sql/dmzStuff/dmzListener.php
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

chmod 644 /etc/systemd/system/dmzListener.service


cat << EOF > "/etc/systemd/system/frontendListener.service"
[Unit]
Description= Starts frontend listener
After=network.target

[Service]
Type=simple
User=root
ExecStart=/bin/php /home/vboxuser/git/rabbitmqphp_example/sql/databaseHandler.php
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

chmod 644 /etc/systemd/system/dmzListener.service

systemctl daemon-reload

systemctl enable dmzListener.service

systemctl enable frontendListener.service

systemctl restart dmzListener.service

systemctl restart frontendListener.service

systemctl status dmzListener.service

systemctl status frontendListener.service

