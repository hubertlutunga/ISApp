<?php

http_response_code(403);
header('Content-Type: text/plain; charset=utf-8');
echo 'Cette page d\'essai Twilio est desactivee pour des raisons de securite.';
exit;