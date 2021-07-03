<!DOCTYPE html>
<html lang="en">
    <head>
        <script src="https://unpkg.com/konva@8.0.4/konva.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Batalha Naval</title>
        <link rel="icon" type="image/png" href="{{secure_asset('img/icon-app.png')}}">
        <link rel="stylesheet" href="{{secure_asset('css/app.css')}}">
    </head>
<body>

    @yield('content')

    <audio id="background-sound" controls loop muted autoplay hidden>
        <source src="{{secure_asset('audio/mar.mp3')}}" type="audio/mpeg">
    </audio>

    <script>
        var bgaudio = document.getElementById("background-sound");

        function enableMute() {
            if (bgaudio.muted){
                bgaudio.muted = false;
            } else {
                bgaudio.muted = true;
            }
        }

        var prevScrollpos = window.pageYOffset;
        window.onscroll = function() {
        var currentScrollPos = window.pageYOffset;
        if (prevScrollpos > currentScrollPos) {
            document.getElementById("navbar").style.top = "0";
        } else {
            document.getElementById("navbar").style.top = "-70px";
        }
        prevScrollpos = currentScrollPos;
        }

    </script>

</body>
</html>
