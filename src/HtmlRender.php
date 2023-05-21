<?php


declare(strict_types=1);

namespace Effectra\Router;

class HtmlRender
{

    /**
     * Get the HTML content for a 404 Not Found response.
     *
     * @return string The HTML content.
     */
    public static function notFoundHTML(): string
    {
        return <<<'HTML'
<html>
    <head>
        <title>404 Not Found</title>
    </head>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin: 50px;
        }

        h1 {
            color: #333;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 20px auto;
            width: 50%;
        }

        p {
            color: #666;
        }
    </style>
    <body>
        <h1>404 | Not Found</h1>
        <hr>
        <p>The requested URL was not found on this server.</p>
    </body>
</html>
HTML;
    }

    /**
     * Get the HTML content for a 503 Internal Server Error response.
     *
     * @return string The HTML content.
     */
    public static function internalServerErrorHTML(): string
    {
        return <<<'HTML'
<html>
    <head>
        <title>503 Internal Server Error</title>
    </head>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            margin: 50px;
        }

        h1 {
            color: #333;
        }

        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 20px auto;
            width: 50%;
        }

        p {
            color: #666;
        }
    </style>
    <body>
        <h1>503 | Internal Server Error</h1>
        <hr>
        <p>The requested URL was not found on this server.</p>
    </body>
</html>
HTML;
    }
}
