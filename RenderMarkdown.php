<?php

class GitHub
{
    private $apiUrl = 'https://api.github.com/markdown';

    private function getMarkdownUrls($markdownString)
    {
        $urlArray = array();

        $markdownRest = $markdownString;

        while (str_contains($markdownRest, 'http'))
        {
            // Get starting and ending positions
            $linkPosStart = strpos($markdownRest, '](');
            $linkPosEnd = strpos($markdownRest, ')', $linkPosStart);

            // Split the string
            $extractedUrl = substr($markdownRest, $linkPosStart, $linkPosEnd - $linkPosStart);

            // Trimm the URL
            $trimmedUrl = substr($extractedUrl, 2);
            $urlArray[] = $trimmedUrl;

            // Parse rest of string
            $markdownRest = substr($markdownRest, $linkPosEnd);
        }

        return $urlArray;
    }
    
    private function formatUrls($urlArray, $repoUrl, $rawRepoUrl)
    {
        $formattedUrlArray = array();

        foreach ($urlArray as $url)
        {
            if (str_starts_with($url, 'http'))
                $formattedUrlArray[] = $url;
            else if (str_starts_with($url, 'mailto'))
                $formattedUrlArray[] = $url;
            else
            {
                if (str_ends_with($url, 'png'))         // PNG
                    $formattedUrlArray[] = $rawRepoUrl . $url;
                else if (str_ends_with($url, 'jpg'))    // JPG
                    $formattedUrlArray[] = $rawRepoUrl . $url;
                else if (str_ends_with($url, 'gif'))    // GIF
                    $formattedUrlArray[] = $rawRepoUrl . $url;
                else if (str_ends_with($url, 'mov'))    // MOV
                    $formattedUrlArray[] = $rawRepoUrl . $url;
                else if (str_ends_with($url, 'mp4'))    // MP4
                    $formattedUrlArray[] = $rawRepoUrl . $url;
                else
                    $formattedUrlArray[] = $repoUrl . $url;
            }
        }

        return $formattedUrlArray;
    }

    private function getMarkdownString($markdownFileUrl)
    {
        // Trim Raw MarkdownFileUrl
        $rawMarkdownFileUrl = 'https://raw.githubusercontent.com' . str_replace(
            array('https://github.com', 'github.com', '/blob'),
            array('', '', ''),
            $markdownFileUrl
        );

        // Trim MarkdownFileUrl
        $markdownFileUrl = 'https://github.com' . str_replace(
            array('https://github.com', 'github.com'),
            array('', ''),
            $markdownFileUrl
        );

        // URL File Segment
        $urlSegment = explode('/', $markdownFileUrl);
        $urlSegmentFile = $urlSegment[count($urlSegment) - 1];

        // Get Repository URLs
        $repoUrl = trim(str_replace($urlSegmentFile, '', $markdownFileUrl), '/');    
        $rawRepoUrl = str_replace($urlSegmentFile, '', $rawMarkdownFileUrl);

        // Donwload Markdown
        $markdownString = file_get_contents($rawMarkdownFileUrl);
        
        // Find links in markdown
        $urlArray = $this->getMarkdownUrls($markdownString);
        $formattedUrlArray = $this->formatUrls($urlArray, $repoUrl, $rawRepoUrl);

        for ($i = 0; $i < count($urlArray); $i++)
        {
            $markdownString = str_replace($urlArray[$i], $formattedUrlArray[$i], $markdownString);
        }
        
        return $markdownString;
    }

    private function getHtml($markdownString, $userAgent)
    {            
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode(array('text' => $markdownString)),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'User-Agent: ' . $userAgent
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return $response;
    }

    public function renderMarkdown($markdownFileUrl, $userAgent)
    {
        // Get MarkdownString
        $markdownString = $this->getMarkdownString($markdownFileUrl);

        // Convert MarkdownString to HTML
        $markdownHtml = $this->getHtml($markdownString, $userAgent);

        // Return HTML
        return $markdownHtml;
    }
}

?>