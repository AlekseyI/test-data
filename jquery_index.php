<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        .red-border {
            border: solid;
            border-color: red;
        }
        .green-border {
            border: solid;
            border-color: green;
        }
        .blue-border {
            border: solid;
            border-color: blue;
        }
    </style>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script>
        (function ($) {
            let methods = {
                url: function (argument) {
                    if (!argument['checkInterval'])
                    {
                        argument['checkInterval'] = 30;
                    }
                    if (!argument['sendInterval'])
                    {
                        argument['sendInterval'] = 3000;
                    }
                    let isMouseMoved = false;
                    let isSend = false;
                    let mouse_info = {
                        x: undefined,
                        y: undefined,
                        time: undefined
                    };
                    let positions = [];

                    this.mouseenter(function() {
                        isMouseMoved = true;
                    });

                    this.mousemove(function(e) {
                        mouse_info =  {
                            x: e.clientX,
                            y: e.clientY,
                            time: 0
                        };
                    });

                    this.mouseleave(function() {
                        isMouseMoved = false;
                    });

                    setInterval(() => {
                        if (isMouseMoved && !isSend)
                        {
                            positions.push(mouse_info);

                            if (positions.length > 1)
                            {
                                let last = positions[positions.length - 1];
                                let preLast = positions[positions.length - 2];
                                if (last.x == preLast.x && last.y == preLast.y)
                                {
                                    positions.pop();
                                    last.time += argument['checkInterval'];
                                }
                            }

                            console.log(positions);
                        }
                    }, argument['checkInterval']);

                    setInterval(() => {
                        if (positions.length > 0)
                        {
                            isSend = true;
                            $.ajax({
                                type: 'POST',
                                url: 'index.php',
                                data: {'mouse_info': JSON.stringify(positions)},
                                success: function ()
                                {
                                    positions = [];
                                    isSend = false;
                                    console.log('success');
                                },
                                error: function ()
                                {
                                    isSend = false;
                                    console.log('error');
                                }
                            });
                        }
                    }, argument['sendInterval']);
                }
            };

            $.fn.scanner = function (argument) {
                if (typeof argument === 'object')
                {
                    return methods.url.apply(this, arguments);
                }
                else
                {
                    $.error('Unknown argument ' + argument + 'in jQuery.scanner');
                }
            };
        })(jQuery);

        window.onload = function ()
        {
            console.log($('#asd').scanner({url: 'asd'}));
        }

    </script>
    <title>Title</title>
</head>
<body>
<div id="asd">
    <div class="red-border">
        <h1>Test</h1>
    </div>
    <div class="green-border">
        <h2>Test</h2>
    </div>
    <div class="blue-border">
        <h3>Test</h3>
    </div>
</div>
</body>
</html>