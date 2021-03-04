<!DOCTYPE html>
<html>
    <head>
        <title>Spotify Abal-abal</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="x-icon" href="favicon.ico" />
        <style>
            body {background: #222;color: #fff;font-family: arial;}
            #sedang-main {
                padding: 10px;
                font-size: 24px;
                font-weight: bold;
                color: #222222;
                background: #f1f3f4;
                margin-bottom: -24px;
            }
            #audio-player {width:100%;margin: 20px auto;border-radius: 0;
                           background: #f1f3f4;}
            #playlist {list-style: none;margin: 0;padding: 0;}
            #playlist li {padding: 0px;border-bottom: 1px solid #999;}
            #playlist li a {padding: 10px; text-decoration: none;color: #999;display:block;}
            #playlist li a:hover {background: #555;color: #fff;}
            #cariLagu {width: 100%;
                       background: #222;
                       border: 0;
                       border-bottom: 1px solid #555;
                       padding: 10px;
                       color: #fff;}
            #container-player {
                position: fixed;
                width: 100%;
                bottom: -24px;
                left: 0;
                margin: 0;
            }
            .duration{float:right;margin-top: 10px;color: #ddd;margin-right:10px;}
            #visualizer {
                position: fixed;
                left: 0;
                bottom: 0;
                width: 100%;
                height: 100%;
                z-index: -1;
                opacity: .5;
            }

            .mp3-controls {
                position: fixed;
                bottom: 70px;
                left: 10px;
            }
            
            @media (max-width: 600px) {
               .mp3-controls {
                    position: fixed;
                    top: 0px;
                    left: 0px;
                    background-color: white;
                    width: 100%;
                    height: 64px;
                }
                
                .mp3-controls input {
                    background-color: transparent!important;
                    border-color: transparent!important;
                    outline: transparent!important;
                    color: black;
                    font-weight: bold;
                    font-size: 300%;
                }
                
                body {
                    margin-top: 80px;
                }
            }
            
            #rnd[is_random='si'] {
                color: red;
            }
            
            #rnd[is_random='no'] {
                color: black;
            }
        </style>
    </head>
    <body>
        <canvas id="visualizer"></canvas>
        <div id="container-player">
            <input type="checkbox" id="inputRandom" title="acak" style="position:fixed;top:0;right:0;" />
            <input id="cariLagu" type="text" placeholder="Search..">            
            <marquee behavior="alternate" id="sedang-main">Judul Lagu</marquee>
            <audio controls id="audio-player" style="clear:both;">
                <source id="audio-source">
                Browser anda tidak mendukung, silakan gunakan browser versi jaman now
            </audio>
            <div class="mp3-controls">
                <input id="rewindbutton" type=button onclick="backward()" value="&lt;&lt;">
                <input id="rewindbutton" type=button onclick="previous()" value="&lt;">
                <input id="forwardbutton" type=button onclick="next()" value="&gt;">
                <input id="forwardbutton" type=button onclick="forward()" value="&gt;&gt;">
                <input id="rnd" type="button" value="R" onclick="shuffle(this)">
            </div>
        </div>


        <?php
        // ref: http://www.zedwood.com/article/php-calculate-duration-of-mp3
        // require_once "mp3file.class.php";
        error_reporting(-1);

        $dir = "playlists/";
        if (is_dir($dir)) {
            // if ($buka = opendir($dir)) {
                echo '<ul id="playlist" dir="'.$dir.'">';
                $dirs = scandir($dir);
            
                // echo print_r($dirs, true);
                // while (($file = readdir($dir)) !== false) {
                foreach($dirs as $file) {
                    if(substr($file, 0, 1) == '.') continue;
                    $mp3_folder = $dir.$file;
                    
                    // echo "Dir: ".$file;
                    // echo print_r($dirs, true);
                    
                    if(is_dir($mp3_folder)) {
                        $mp3_dir = scandir($mp3_folder);
                        $cur_dir = str_replace($dir, '', $mp3_folder);
                        
                        /*
                        echo "<pre><code>";
                        echo "File: ".$file.PHP_EOL;
                        echo print_r($mp3_dir, true);
                        echo "</code></pre>";
                        */
                        
                        foreach($mp3_dir as $mp3file) {
                            if (strpos($mp3file, '.mp3')) {
                                // echo $mp3file;
                                $f = $cur_dir.'/'.$mp3file;
                                //echo '<li><small class="duration">' . MP3File::formatTime($duration) . '</small><a href="javascript:void(0)">' . $file . '</a></li>';
                                echo '<li><a href="javascript:void(0)">' . $f . '</a></li>';
                            }
                        }
                    } else {
                        //$mp3file = new MP3File('./'.$dir.$file);
                        //$duration = $mp3file->getDuration(); //(slower) for VBR (or CBR)
                        if (strpos($file, '.mp3')) {
                            $f = str_replace($dir, '', $file);
                            //echo '<li><small class="duration">' . MP3File::formatTime($duration) . '</small><a href="javascript:void(0)">' . $file . '</a></li>';
                            echo '<li><a href="javascript:void(0)">' . $f . '</a></li>';
                        }
                    }
                }
                echo '</ul>';
                // closedir($buka);
            // } else die("Error");
        } else die("Error");
        ?>


        <script src="jquery-3.3.1.min.js"></script>
        <script>
            var current_index = 0, max, folder, urutan, file, mainkan, context;
            
            function shuffle(el) {
                document.getElementById('inputRandom').checked = !$('#inputRandom').prop('checked');
                el.setAttribute('is_random', $('#inputRandom').prop('checked') ? 'si' : 'no');
            }   
            
            function next() {
                    var is_random = $('#inputRandom').prop('checked');
                    var index = is_random ? getRandomInt(0, max) : current_index + 1 >= max ? 0 : current_index + 1;
                    playAudio(index);
                }
                
                function previous() {
                    var is_random = $('#inputRandom').prop('checked');
                    var index = is_random ? getRandomInt(0, max) : current_index - 1 < 0 ? max - 1 : current_index - 1;
                    playAudio(index);
                }
                
                function backward() {
                    var audio = $('#audio-source');
                    audio.currentTime -= 5;
                  }

                  function forward() {
                    var audio = $('#audio-source');
                    audio.currentTime += 5;
                  }
            
                            function playAudio(urutan) {
                    current_index = urutan;
                    
                    tandaiTerpilih(urutan);
                    file = $('#playlist a:eq(' + urutan + ')').text();
                    mainkan = folder + file;
                    $('#sedang-main').html(file);
                    $('#audio-source').prop('src', mainkan);
                    $('#audio-player').trigger('load');
                    $('#audio-player').trigger('play');

                    makeVisualizer();
                }
                
                function makeVisualizer() {
                    if(context != null) return;
                    
                    var audio = document.getElementById("audio-player");
//                var audio = document.createElement(audio);
                    //ref: https://codepen.io/nfj525/pen/rVBaab
                    context = new AudioContext();
                    var src = context.createMediaElementSource(audio);
                    var analyser = context.createAnalyser();

                    var canvas = document.getElementById("visualizer");
                    canvas.width = window.innerWidth;
                    canvas.height = window.innerHeight;
                    var ctx = canvas.getContext("2d");

                    src.connect(analyser);
                    analyser.connect(context.destination);

                    analyser.fftSize = 256;

                    var bufferLength = analyser.frequencyBinCount;
                    console.log(bufferLength);

                    var dataArray = new Uint8Array(bufferLength);

                    var WIDTH = canvas.width;
                    var HEIGHT = canvas.height;

                    var barWidth = (WIDTH / bufferLength) * 2.5;
                    var barHeight;
                    var x = 0;

                    function renderFrame() {
                        requestAnimationFrame(renderFrame);

                        x = 0;

                        analyser.getByteFrequencyData(dataArray);

                        ctx.fillStyle = "#000";
                        ctx.fillRect(0, 0, WIDTH, HEIGHT);

                        for (var i = 0; i < bufferLength; i++) {
                            barHeight = dataArray[i];

                            var r = barHeight + (25 * (i / bufferLength));
                            var g = 250 * (i / bufferLength);
                            var b = 50;

                            ctx.fillStyle = "rgb(" + r + "," + g + "," + b + ")";
                            ctx.fillRect(x, HEIGHT - barHeight, barWidth, barHeight);

                            x += barWidth + 1;
                        }
                    }
                    renderFrame();
                }
            
                            function getRandomInt(min, max) {
                    min = Math.ceil(min);
                    max = Math.floor(max);
                    return Math.floor(Math.random() * (max - min + 1)) + min;
                }

                function tandaiTerpilih(urutan) {
                    $('#playlist li').css('background-color', 'transparent');
                    $('#playlist li').filter(function (index) {
                        return index === urutan;
                    }).css('background-color', '#037');
                }
            
            $(document).ready(function () {
                $('#playlist').css('margin-bottom', eval($('#container-player').height() - 15) + "px");
                $('#visualizer').css('bottom', eval($('#container-player').height() - 15) + "px");

                folder = "<?= $dir ?>";
                urutan = 0;
                max = $('#playlist a').length;
                file = '', mainkan = "";

                $('#playlist a').on('click', function () {
                    urutan = $(this).parent().prevAll().length;
                    playAudio(urutan);
                });

                $('#audio-player').on('ended', function () {
                    var isRandom = $('#inputRandom').prop('checked');
                    if (isRandom) {
                        urutan = getRandomInt(0, max);
                    } else {
                        urutan++;
                        if (urutan == $('#playlist a').length) {
                            urutan = 0;
                        }
                    }
                    playAudio(urutan);
                });

                
                $("#cariLagu").on("keyup", function () {
                    var value = $(this).val().toLowerCase();
                    $("#playlist li").filter(function () {
                        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                    });
                });






            });
        </script>
    </body>
</html>
