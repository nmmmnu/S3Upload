S3 Uploader in PHP
===============
This S3 Uploader in PHP is a standalone class without any dependencies.

It is tested against DigitalOcean Spaces and currently can upload successfully.
Usage is like that:

    <?php
    require "s3_upload_working.php";

    $s3 = new S3Uploader("ams3.digitaloceanspaces.com", "xxxxxxx", "xxxxx", "bucket");

    $r = $s3->uploadFile("/home/nmmm/despicable-me-2-minions_a-G-10438535-0.jpg", "/minion.jpg", "image/jpeg");

    print_r($r);

