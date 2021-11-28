# Markdown Renderer
The purpose of this tool is to render markdown files (for now just GitHub) using their own respective APIs to achieve a true to live visual.

## Usage
You either integrate the script on your webserver, set up a dedicated server to hsot this tool or you use https://markdown.tools.rehren.group/

If you decide to go with my domain or to host your own service, you will need to append the path to your markdown file.
> e.g. https://markdown.tools.rehren.group/FriedrichRehren/Markdown-Renderer/blob/master/README.md

## Installation
You have to options, to use this tool with your own services.
1. You dowload the *WebIndex.php* and *RenderMarkdown.php* and place them at the root of your Webserver.

    You will need to configure Apache with the following rules.

        RewriteEngine On
        # If an existing asset or directory is requested go to it as it is
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d
        RewriteRule ^ - [L]

        # If the requested resource doesn't exist, use index.php
        RewriteRule ^ /index.php

2. You use the *RenderMarkdown.php* and call it from any other PHP file on your server.