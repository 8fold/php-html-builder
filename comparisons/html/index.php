<?php
$start = hrtime(true);
?>
<!doctype html>
<html lang="en">
    <head>
        <title>Page title here</title>
        <meta charset="utf-8">
    </head>
    <body>
        <h1>Page title here</h1>
        <p><a href="#bottom">time</a></p>
        <artcle>
            <section>
                <h2>Blog title 1</h2>
                <p>Brief description of content for blog post 1</p>
                <p><a href="#">Read blog post 1</a></p>
            </section>
            <section>
                <h2>Blog title 2</h2>
                <p>Brief description of content for blog post 2</p>
                <p><a href="#">Read blog post 2</a></p>
            </section>
            <section>
                <h2>Blog title 3</h2>
                <p>Brief description of content for blog post 3</p>
                <p><a href="#">Read blog post 3</a></p>
            </section>
            <section>
                <h2>Blog title 4</h2>
                <p>Brief description of content for blog post 4</p>
                <p><a href="#">Read blog post 4</a></p>
            </section>
            <section>
                <h2>Blog title 5</h2>
                <p>Brief description of content for blog post 5</p>
                <p><a href="#">Read blog post 5</a></p>
            </section>
            <section>
                <h2>Blog title 6</h2>
                <p>Brief description of content for blog post 6</p>
                <p><a href="#">Read blog post 6</a></p>
            </section>
            <section>
                <h2>Blog title 7</h2>
                <p>Brief description of content for blog post 7</p>
                <p><a href="#">Read blog post 7</a></p>
            </section>
            <section>
                <h2>Blog title 8</h2>
                <p>Brief description of content for blog post 8</p>
                <p><a href="#">Read blog post 8</a></p>
            </section>
            <section>
                <h2>Blog title 9</h2>
                <p>Brief description of content for blog post 9</p>
                <p><a href="#">Read blog post 9</a></p>
            </section>
            <section>
                <h2>Blog title 10</h2>
                <p>Brief description of content for blog post 10</p>
                <p><a href="#">Read blog post 10</a></p>
            </section>
        </artcle>
<?php
$end = hrtime(true);

$elapsed = $end - $start;
$ms      = $elapsed/1e+6;

print('<p id="bottom"><b>' . $ms . 'ms</b></p>');
?>
    </body>
</html>
