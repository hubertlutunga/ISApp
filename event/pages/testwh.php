<?php 


 
    // Update the path below to your autoload.php,
    // see https://getcomposer.org/doc/01-basic-usage.md
    require_once '/path/to/vendor/autoload.php';
    use Twilio\Rest\Client;

    $sid    = "AC5cbb94f85695ce16d97ce2ca2c3f7db0";
    $token  = "2fc99f87d42f61c691c01df995fb8290";
    $twilio = new Client($sid, $token);

    $message = $twilio->messages
      ->create("whatsapp:+243810678785", // to
        array(
          "from" => "whatsapp:+14155238886",
          "contentSid" => "HXb5b62575e6e4ff6129ad7c8efe1f983e",
          "contentVariables" => "{"1":"12/1","2":"3pm"}",
          "body" => "Your Message"
        )
      );

print($message->sid);




?>