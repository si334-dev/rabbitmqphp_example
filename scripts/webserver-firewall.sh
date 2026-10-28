#!/bin/bash
#only activate for testing this will block MOSTLY EVERYTHING besides rabbitmq, type sudo ufw disable to deactivate(i think)
sudo apt -y install ufw

sudo ufw allow 5672
sudo ufw allow 5671

sudo ufw allow http
sudo ufw allow https

sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw enable

sudo ufw status verbose

