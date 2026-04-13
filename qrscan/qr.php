
                <?php

                  include('../phpqrcode/qrlib.php'); 
                  $PNG_TEMP_DIR = 'temp/';
                  if (!file_exists($PNG_TEMP_DIR))
                      mkdir($PNG_TEMP_DIR);

                  $codeString = 'https://www.akilischool.com';

                  $filename = $PNG_TEMP_DIR . 'test.png';

                  $filename = $PNG_TEMP_DIR . 'test' . md5($codeString) . '.png';

                 QRcode::png($codeString, $filename);


                  // Qrcode::size(200)->generate("https://www.akilischool.com");

                  echo '<img src="' . $PNG_TEMP_DIR . basename($filename) . '" /><hr/>';
               

                ?>