<?php
$files = [
    'medical.php', 'travel.php', 'summary.php', 'payment.php', 
    'otp.php', 'nafath.php', 'password.php', 'raj.php', 
    'StepOne.php', 'StepTwo.php', 'StepThird.php', 'StepFourth.php', 'StepFifth.php',
    'medical-info.php', 'travel-companies.php', 'medical-companies.php', 'index-details.php', 'index-types.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'chat_widget.php') === false) {
            $content = str_replace('</body>', "<?php include 'chat_widget.php'; ?>\n</body>", $content);
            file_put_contents($file, $content);
            echo "Updated $file\n";
        } else {
            echo "Skipped $file (already has it)\n";
        }
    }
}
?>
