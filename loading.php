<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>




/* Center the loading animation */
#loadingContainer {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Ensures it's centered */
    width: 112px;
    height: 112px;
    z-index: 9999; /* Ensures it stays on top of everything else */
    pointer-events: none; /* Prevents interaction with the underlying content */
}

/* Loader style */
.loader {
    width: 112px;
    height: 112px;
    position: relative;
}

.box1, .box2, .box3 {
    border: 16px solid #e3e3e3;
    box-sizing: border-box;
    position: absolute;
    display: block;
}

.box1 {
    width: 112px;
    height: 48px;
    margin-top: 64px;
    margin-left: 0px;
    animation: abox1 4s 1s forwards ease-in-out infinite;
}

.box2 {
    width: 48px;
    height: 48px;
    margin-top: 0px;
    margin-left: 0px;
    animation: abox2 4s 1s forwards ease-in-out infinite;
}

.box3 {
    width: 48px;
    height: 48px;
    margin-top: 0px;
    margin-left: 64px;
    animation: abox3 4s 1s forwards ease-in-out infinite;
}

/* Keyframe animations for loader */
@keyframes abox1 {
    0% { width: 112px; height: 48px; margin-top: 64px; margin-left: 0px; }
    12.5% { width: 48px; height: 48px; margin-top: 64px; margin-left: 0px; }
    25% { width: 48px; height: 48px; margin-top: 64px; margin-left: 0px; }
    37.5% { width: 48px; height: 48px; margin-top: 64px; margin-left: 0px; }
    50% { width: 48px; height: 48px; margin-top: 64px; margin-left: 0px; }
    62.5% { width: 48px; height: 48px; margin-top: 64px; margin-left: 0px; }
    75% { width: 48px; height: 112px; margin-top: 0px; margin-left: 0px; }
    87.5% { width: 48px; height: 48px; margin-top: 0px; margin-left: 0px; }
    100% { width: 48px; height: 48px; margin-top: 0px; margin-left: 0px; }
}

@keyframes abox2 {
    0% { width: 48px; height: 48px; margin-top: 0px; margin-left: 0px; }
    12.5% { width: 48px; height: 48px; margin-top: 0px; margin-left: 0px; }
    25% { width: 48px; height: 48px; margin-top: 0px; margin-left: 0px; }
    37.5% { width: 48px; height: 48px; margin-top: 0px; margin-left: 0px; }
    50% { width: 112px; height: 48px; margin-top: 0px; margin-left: 0px; }
    62.5% { width: 48px; height: 48px; margin-top: 0px; margin-left: 64px; }
    75% { width: 48px; height: 48px; margin-top: 0px; margin-left: 64px; }
    87.5% { width: 48px; height: 48px; margin-top: 0px; margin-left: 64px; }
    100% { width: 48px; height: 48px; margin-top: 0px; margin-left: 64px; }
}

@keyframes abox3 {
    0% { width: 48px; height: 48px; margin-top: 0px; margin-left: 64px; }
    12.5% { width: 48px; height: 48px; margin-top: 0px; margin-left: 64px; }
    25% { width: 48px; height: 112px; margin-top: 0px; margin-left: 64px; }
    37.5% { width: 48px; height: 48px; margin-top: 64px; margin-left: 64px; }
    50% { width: 48px; height: 48px; margin-top: 64px; margin-left: 64px; }
    62.5% { width: 48px; height: 48px; margin-top: 64px; margin-left: 64px; }
    75% { width: 48px; height: 48px; margin-top: 64px; margin-left: 64px; }
    87.5% { width: 48px; height: 48px; margin-top: 64px; margin-left: 64px; }
    100% { width: 112px; height: 48px; margin-top: 64px; margin-left: 0px; }
}


    </style>

</head>
<body>
    <!-- From Uiverse.io by alexruix --> 
<div class="loader">
  <div class="box1"></div>
  <div class="box2"></div>
  <div class="box3"></div>
</div>
</body>
</html>