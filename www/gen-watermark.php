<?php
@mkdir(__DIR__ . '/public/images', 0777, true);
$img = imagecreatetruecolor(800, 800);
imagesavealpha($img, true);
$transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefill($img, 0, 0, $transparent);
$white = imagecolorallocatealpha($img, 255, 255, 255, 60);

for ($i = 0; $i < 10; $i++) {
    for ($j = 0; $j < 10; $j++) {
        imagestring($img, 5, $i * 120, $j * 120, 'PROVA DE USO', $white);
    }
}
imagepng($img, __DIR__ . '/public/images/watermark-default.png');
echo "Watermark generated successfully.\n";
