<?php
$errors = array();
$options = array(
    "host" => "", "port" => "",
    "username" => "", "password" => "",
    "secure" => "",
    "from" => "",
    "fromName" => "",
    "testMail" => ""
);
if ($config->hasSection("mailer")) {
    if ($smtp = $config->get("mailer", "smtp", array())) {
        $options = array_merge($options, $smtp);
    }
    if ($from = $config->get("mailer", "from", null)) {
        $options["from"] = $from;
    }
    if ($fromName = $config->get("mailer", "fromName", null)) {
        $options["fromName"] = $fromName;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $options = array_merge($options, array_map("trim", $_POST));
    if (isset($_POST["testSMTP"])) {
        require_once "PHPMailer/class.phpmailer.php";
        $mailer = new PHPMailer($exceptions=true);
        $mailer->setLanguage("fr", DOCUMENT_ROOT."/lib/PHPMailer/language/");
        $mailer->CharSet = "utf-8";
        if (!empty($options["host"])) {
            $mailer->Host = $options["host"];
            $mailer->isSMTP();
        }
        if (!empty($options["port"])) {
            $mailer->Port = $options["port"];
            $mailer->isSMTP();
        }
        if (!empty($options["username"])) {
            $mailer->SMTPAuth = true;
            $mailer->Username = $options["username"];
        }
        if (!empty($options["password"])) {
            $mailer->SMTPAuth = true;
            $mailer->Password = $options["password"];
        }
        if (!empty($options["secure"])) {
            $mailer->SMTPSecure = $options["secure"];
        }
        if (!empty($options["from"])) {
            $mailer->Sender = $options["from"];
            $mailer->From = $options["from"];
        }
        if (empty($_POST["testMail"])) {
            $errors["testMail"] = "Indiquez une adresse e-mail pour l'envoi du test.";
        } else {
            $mailer->clearAddresses();
            $mailer->addAddress($_POST["testMail"]);
            if ($options["fromName"]) {
                $mailer->FromName = $options["fromName"];
            }
            $mailer->Subject = "Test d'envoi de mail";
            $mailer->Body = "Bravo.\nVotre configuration mail est validée.";
            try {
                $mailer->send();
                $testSended = true;
            } catch (phpmailerException $e) {
                $testError = $e->getMessage();
            }
        }
    } else {
        $config->set("mailer", "smtp", array(
            "host" => $options["host"], "port" => $options["port"],
            "username" => $options["username"], "password" => $options["password"],
            "secure" => $options["secure"]
        ));
        $config->set("mailer", "from", $options["from"]);
        $config->set("mailer", "fromName", $options["fromName"]);
        $config->save();
        header("LOCATION: ?mod=admin&a=mail");
        exit;
    }
}