<?php
$start = hrtime(true);

$pageTitle = "Page title here";

$posts = [
    [
        "title" => "Blog title 1",
        "description" => "Brief description for blog post 1",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 1"
        ]
    ],
    [
        "title" => "Blog title 2",
        "description" => "Brief description for blog post 2",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 2"
        ]
    ],
    [
        "title" => "Blog title 3",
        "description" => "Brief description for blog post 3",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 3"
        ]
    ],
    [
        "title" => "Blog title 4",
        "description" => "Brief description for blog post 4",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 4"
        ]
    ],
    [
        "title" => "Blog title 5",
        "description" => "Brief description for blog post 5",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 5"
        ]
    ],
    [
        "title" => "Blog title 6",
        "description" => "Brief description for blog post 6",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 6"
        ]
    ],
    [
        "title" => "Blog title 7",
        "description" => "Brief description for blog post 7",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 7"
        ]
    ],
    [
        "title" => "Blog title 8",
        "description" => "Brief description for blog post 8",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 8"
        ]
    ],
    [
        "title" => "Blog title 9",
        "description" => "Brief description for blog post 9",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 9"
        ]
    ],
    [
        "title" => "Blog title 10",
        "description" => "Brief description for blog post 10",
        "url" => [
            "href" => "#",
            "text" => "Read blog post 10"
        ]
    ]
];
?>
<!doctype html>
<html lang="en">
    <head>
        <title><?php print($pageTitle); ?></title>
        <meta charset="utf-8">
    </head>
    <body>
        <h1><?php print($pageTitle); ?></h1>
        <p><a href="#bottom">time</a></p>
        <artcle>
<?php foreach ($posts as $post): ?>
<section>
    <h2><?php print($post['title']); ?></h2>
    <p><?php print($post['description']); ?></p>
    <p><a href="<?php print($post['url']['href']); ?>"><?php print($post['url']['text']); ?></a></p>
</section>
<?php endforeach; ?>
        </artcle>
<?php
$end = hrtime(true);

$elapsed = $end - $start;
$ms      = $elapsed/1e+6;

print('<p id="bottom"><b>' . $ms . 'ms</b></p>');
?>
    </body>
</html>
