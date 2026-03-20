<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use RedBeanPHP\OODBBean;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

function rabbitMqConnection(): AMQPStreamConnection
{
    do {
        try {
            $connection = new AMQPStreamConnection(
                getenv('RABBITMQ_HOST'),
                getenv('RABBITMQ_PORT'),
                getenv('RABBITMQ_USERNAME'),
                getenv('RABBITMQ_PASSWORD')
            );
        } catch (AMQPIOException) {
            sleep(5);
            echo 'Retrying' . PHP_EOL;
        }
    } while (!isset($connection));

    return $connection;
}

function sendMailTo(OODBBean $student): void
{
    $mensagem = <<<FIM
    Hola, $student->name! Su pago ha sido confirmado y su matrícula ha sido creada con éxito.
    Para acceder a su cuenta y comenzar a estudiar con nosotros, acceda a: http://localhost:4200/login.
    Sus datos de acceso son:
    Correo electrónico: $student->email
    Contraseña: 123456
    
    ¡Buena suerte!
    FIM;

    $usuario = getenv('GMAIL_USER');

    $email = (new Email())
        ->from($usuario)
        ->to($student->email)
        ->subject('Matrícula confirmada')
        ->text($mensagem);

    $senha = urlencode(getenv('GMAIL_PASSWORD'));
    $transport = Transport::fromDsn("gmail+smtp://$usuario:$senha@default");
    $mailer = new Mailer($transport);
    $mailer->send($email);
}
