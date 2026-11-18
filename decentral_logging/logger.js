const amqp = require('amqplib');
const fs = require('fs');
const os = require('os');

const config = {
    host: '100.92.251.116',
    user: 'admin',
    pass: 'admin',
    vhost: 'testHost'
};

async function startLogger() {
    try {
        const url = `amqp://${config.user}:${config.pass}@${config.host}:5672/${config.vhost}`;
        const connection = await amqp.connect(url);
        const channel = await connection.createChannel();

        const exchangeName = 'logExchange';
        const queueName = os.hostname()+'_log_queue';

        await channel.assertExchange(exchangeName, 'fanout', { durable: true });
        await channel.assertQueue(queueName, { durable: true });
        await channel.bindQueue(queueName, exchangeName, '');

        console.log("Waiting for logs...");

        channel.consume(queueName, (msg) => {
            if (msg !== null) {
                const logMessage = msg.content.toString();
                const time = new Date().toISOString();
                
                fs.appendFileSync('decentralized.log', `[${time}] ${logMessage}\n`);
                
                console.log("Logged:", logMessage);
                channel.ack(msg);
            }
        });

    } catch (error) {
        console.error("Connection error:", error.message);
    }
}

startLogger();