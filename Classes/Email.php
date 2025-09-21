<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email{
    protected $email;
    protected $nombre;
    protected $token;

    public function __construct($email, $nombre, $token){
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = 'bdb7811d1a35f8';
        $mail->Password = '46cb859a53d63f';

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Confirma tu cuenta';

        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong>, has creado tu cuenta en UpTask, solo debes confirmarla en el siguiente enlace</p>';
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/confirmar?token=" . $this->token . "'>Confirmar Cuenta</a></p>";
        $contenido .= '<p>Si tu no creaste esta cuenta, puedes ignorar este mensaje.</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar email
        $mail->send();
    }


    public function enviarInstrucciones(){

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom('cuentas@uptask.com');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Reestablece tu password';

        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong>, parece que has olvidado tu password, presiona el siguiente enlace para reestablecerla.</p>';
        $contenido .= "<p>Presiona aquí: <a href= '" . $_ENV['APP_URL'] . "/reestablecer?token=" . $this->token . "'>Reestablecer Password</a></p>";
        $contenido .= '<p>Si no tu solicitaste reestablecer tu password, puedes ignorar este mensaje.</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar email
        $mail->send();
    }
}