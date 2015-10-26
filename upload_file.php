<!-- /*************************************************

Signsend - The signature capture webapp sample using HTML5 Canvas.

Author: Jack Wong <jack.wong@zetakey.com>
Copyright (c): 2014 Zetakey Solutions Limited, all rights reserved

 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

You may contact the author of Jack Wong by e-mail at:
jack.wong@zetakey.com

The latest version can obtained from:
https://github.com/jackccwong/signsend

The live demo is located at:
http://apps.zetakey.com/signsend

**************************************************/ -->


<?php

function geturlonly() {
    $urlpath = explode('/', $_SERVER['PHP_SELF']);
    array_pop($urlpath);
    $scriptname = implode("/", $urlpath);
    $http_protocol = 'http';
    if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] == 443)){
      $http_protocol = 'https';
    }
    return $http_protocol . "://" . $_SERVER["HTTP_HOST"] . $scriptname . "/";
}

function multi_attach_mail($to, $sendermail, $subject, $message, $files) {
    // email fields: to, from, subject, and so on
    $from = $sendermail;
    $headers = "From: $from";

    // boundary
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    // headers for attachment
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

    // multipart boundary
    $message = "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";

    if (is_file($files)) {
        $message .= "--{$mime_boundary}\n";
        $fp = @fopen($files, "rb");
        $data = @fread($fp, filesize($files));
        @fclose($fp);
        $data = chunk_split(base64_encode($data));
        $message .= "Content-Type: application/octet-stream; name=\"" . basename($files) . "\"\n" . "Content-Description: " . basename($files[$i]) . "\n" . "Content-Disposition: attachment;\n" . " filename=\"" . basename($files) . "\"; size=" . filesize($files) . ";\n" . "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        $i = TRUE;
    } else {

        // preparing attachments
        for ($i = 0; $i < count($files); $i++) {
            if (is_file($files[$i])) {
                $message .= "--{$mime_boundary}\n";
                $fp = @fopen($files[$i], "rb");
                $data = @fread($fp, filesize($files[$i]));
                @fclose($fp);
                $data = chunk_split(base64_encode($data));
                $message .= "Content-Type: application/octet-stream; name=\"" . basename($files[$i]) . "\"\n" . "Content-Description: " . basename($files[$i]) . "\n" . "Content-Disposition: attachment;\n" . " filename=\"" . basename($files[$i]) . "\"; size=" . filesize($files[$i]) . ";\n" . "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
            }
        }
    }
    $message .= "--{$mime_boundary}--";
    $returnpath = "-f" . $sendermail;
    $ok = @mail($to, $subject, $message, $headers, $returnpath);
    if ($ok) {
        return $i;
    } else {
        return FALSE;
    }
}

function base64_to_jpeg($base64_string, $output_file) {
    $ifp = @fopen($output_file, "wb");

    $data = explode(',', $base64_string);

    @fwrite($ifp, base64_decode($data[1]));
    @fclose($ifp);
    return $output_file;

}

function add_ZK_mark($inputfile, $outputfile) {

//    var_dump(gd_info());
    $im = @imagecreatefrompng($inputfile);

    $bg = @imagecolorallocate($im, 255, 255, 255);
    $textcolor = @imagecolorallocate($im, 0, 0, 255);

    list($x, $y, $type) = getimagesize($inputfile);

    $txtpos_x = $x - 170;
    $txtpos_y = $y - 20;

    @imagestring($im, 5, $txtpos_x, $txtpos_y, 'Powered by Zetakey', $textcolor);

    $txtpos_x = $x - 145;
    $txtpos_y = 20;

    @imagestring($im, 3, $txtpos_x, $txtpos_y, date("Y-m-d H:i:s"), $textcolor);

    @imagepng($im, $outputfile);

    // Output the image
    //imagejpeg($im);

    @imagedestroy($im);

}
date_default_timezone_set("Asia/Hong_Kong");
$output_file = "captured/signature" . date("Y-m-d-H-i-s-").time(). ".png";
base64_to_jpeg($_POST["image"], $output_file);

add_ZK_mark($output_file, $output_file);

$to = $_POST["email"];
$replyemail = $_POST["replyemail"];
$replyemail = "contact@zetakey.com";

if( (!isset($_POST["email"])) || ($to == "toemail@example.com" ) || ($replyemail == "youremail@example.com")|| ($to == "" )){
    echo("<p>Incorrect email address...</p>");
    echo "<a href=\"index.html\">Sign and Send again!</a>";
    exit;

}

$subject = "You got a captured sigature";
$curdir = dirname($_SERVER['REQUEST_URI']) . "/";
$dir = $_SERVER['SERVER_NAME'] . $curdir;

$urlonly = geturlonly();
$body = "Signed on " . date("Y.m.d H:i:s") . "\n Zetakey Webapp - Sign and Send ".$urlonly."\n";

//$from = "contact@zetakey.com";
$headers = "From: $replyemail" . "\r\nReply-To: $replyemail";

if (multi_attach_mail($to, $replyemail, $subject, $body, $output_file)) {
    //if (mail($to, $subject, $body, $headers)) {
    echo("<p>Message successfully sent to " . $to . " !</p>");
} else {
    echo("<p>Message delivery failed...</p>");
}

echo "<a href=\"index.html\">Sign and Send again!</a>";

exit ;
?>
