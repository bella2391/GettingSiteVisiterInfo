<?php
header('Content-Type: text/css; charset=utf-8'); 

$height = 30;
$width = $height*2.2;
$left = $width-$height;
?>

.toggle_input {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 5;
    opacity: 0;
    cursor: pointer;
}

.toggle_label {
    width: <?php echo $width; ?>px;
    height: <?php echo $height; ?>px;
    background: #aaa;
    position: relative;
    display: inline-block;
    border-radius: 40px;
    transition: 0.4s;
    box-sizing: border-box;
}

.toggle_label:after {
    content: "";
    position: absolute;
    width: <?php echo $height; ?>px;
    height: <?php echo $height; ?>px;
    border-radius: 100%;
    left: 0;
    top: 0;
    z-index: 2;
    background: #fff;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    transition: 0.4s;
}

.toggle_input:checked+.toggle_label {
    background-color: #4BD865;
}

.toggle_input:checked+.toggle_label:after {
    left: <?php echo $left; ?>px;
}

.toggle_button {
    position: relative;
    width: <?php echo $width; ?>px;
    height: <?php echo $height; ?>px;
    margin: auto;
    margin-right:0px;
}

<?php //ここからボタンリバース ?>
.reverse_toggle_input {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 5;
    opacity: 0;
    cursor: pointer;
}

.reverse_toggle_label {
    width: <?php echo $width; ?>px;
    height: <?php echo $height; ?>px;
    background: #4BD865;
    position: relative;
    display: inline-block;
    border-radius: 40px;
    transition: 0.4s;
    box-sizing: border-box;
}

.reverse_toggle_label:after {
    content: "";
    position: absolute;
    width: <?php echo $height; ?>px;
    height: <?php echo $height; ?>px;
    border-radius: 100%;
    right: 0;
    top: 0;
    z-index: 2;
    background: #fff;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
    transition: 0.4s;
}

.reverse_toggle_input:checked+.reverse_toggle_label {
    background-color: #aaa; /*exchange #4BD865*/
}

.reverse_toggle_input:checked+.reverse_toggle_label:after {
    right: <?php echo $left; ?>px;
}

.reverse_toggle_button {
    position: relative;
    width: <?php echo $width; ?>px;
    height: <?php echo $height; ?>px;
    margin: auto;
    margin-right:0px;
}
