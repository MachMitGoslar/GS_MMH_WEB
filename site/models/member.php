<?php
use Kirby\Cms\Block;

class MemberPage extends Page
{
    public function cover()
    {
        return $this->content()->cover()->toFile() ?? $this->image();
    }

    public function description() 
    {
        if (!$this->content()->description()->isNotEmpty()) {
            
            $block = new Block([
                "type" => "text",
                "content" => "Ich hätte so gern eine Beschreibung!!".file_get_contents('http://loripsum.net/api/plaintext')
            ]);
            return $block->content()->body();
        } else {
            return $this->content()->description();
        }
    }

}
?>