# Decentralized logging setup

#1 Install Dependencies 
* Navigate to decentral_logging file in github repository
* sudo apt update
* sudo apt install nodejs npm -y
* npm install amqplib
* node logger.js
* you should see "Waiting for logs.."

#2  RabbitMQ queue
* script auto generates queue with vm's name
* to bind your vm's queue, go to logExchange in exchanges and make sure it's binded to your personal queue
* publish test message in logExchange

#3 Logging file
* Check your terminal that is running logger.js to see if message logged
* in another window, cat decentralized.log, to see all the messages logged 
