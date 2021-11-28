<?php

class GitHub
{
    private $apiUrl = 'https://api.github.com/markdown';
    private $fileUrl;
    private $repoUrl;
    private $rawRepoUrl;
    
    private function formatUrl($url)
    {        
        if (str_starts_with($url, 'mailto'))
            return $url;
        else if (str_contains($url, '://'))
            return $url;   
        else if (str_starts_with($url, '#'))
            return $this->fileUrl . $url;
        else
        {
            if (str_ends_with($url, '.png'))         // PNG
                return $this->rawRepoUrl . $url;
            else if (str_ends_with($url, '.jpg'))    // JPG
                return $this->rawRepoUrl . $url;
            else if (str_ends_with($url, '.gif'))    // GIF
                return $this->rawRepoUrl . $url;
            else if (str_ends_with($url, '.mov'))    // MOV
                return $this->rawRepoUrl . $url;
            else if (str_ends_with($url, '.mp4'))    // MP4
                return $this->rawRepoUrl . $url;
            else if (str_ends_with($url, '.md'))    // MD
                return $this->repoUrl . '/' . $url;
            else
                return $this->repoUrl . $url;
        }
    }

    private function formatMarkdownString($markdownString)
    {
        $markdownRest = $markdownString;

        while (str_contains($markdownRest, ']('))
        {

            // Get starting and ending positions
            $linkPosStart = strpos($markdownRest, '](');
            $linkPosEnd = strpos($markdownRest, ')', $linkPosStart);

            // Split the string
            $extractedUrl = substr($markdownRest, $linkPosStart, $linkPosEnd - $linkPosStart);

            // Trimm and format the URL
            $trimmedUrl = substr($extractedUrl, 2);
            $formattedUrl = $this->formatUrl($trimmedUrl);

            // Replace the URL
            $markdownString = str_replace($trimmedUrl, $formattedUrl, $markdownString);   

            // Parse rest of string
            $markdownRest = substr($markdownRest, $linkPosEnd);
        }

        return $markdownString;
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

        // Save MarkdownFileUrl
        $this->fileUrl = $markdownFileUrl;

        // URL File Segment
        $urlSegment = explode('/', $markdownFileUrl);
        $urlSegmentFile = $urlSegment[count($urlSegment) - 1];

        // Get Repository URLs
        $this->repoUrl = trim(str_replace($urlSegmentFile, '', $markdownFileUrl), '/');    
        $this->rawRepoUrl = str_replace($urlSegmentFile, '', $rawMarkdownFileUrl);

        // Donwload Markdown
        $markdownString = file_get_contents($rawMarkdownFileUrl);
        
        // Format Markdown
        $formattedMarkdown = $this->formatMarkdownString($markdownString);

        return $formattedMarkdown;
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