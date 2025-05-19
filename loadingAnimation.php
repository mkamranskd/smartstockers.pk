<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <style>
    body,
    html {
        height: 100%;
        margin: 0;
        background-color: white;
    }

    img {
        margin: 20px;
    }

    .loading-screen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: white;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        flex-direction: column;
    }



    .loaderr {
        width: 30px;
        aspect-ratio: 1;
        border-radius: 50%;
        background:
            radial-gradient(farthest-side, #007BFF 94%, #0000) top/4px 4px no-repeat,
            conic-gradient(#0000 30%, #007BFF);
        -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 4px), #000 0);
        animation: l13 1s infinite linear;
    }

    @keyframes l13 {
        100% {
            transform: rotate(1turn)
        }
    }
    </style>


</head>

<body>
    <div class="loading-screen" id="loading-screen">
        <img src="img/logo.png" alt="Logo" class="mb-3" width="180">
        <br><br><br><br>
        <div class="loaderr"></div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            document.getElementById("loading-screen").style.display = "none";
        }, 3000);
    });
    </script>
</body>

</html>