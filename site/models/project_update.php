<?php 

class ProjectUpdatePage extends Page
{
    public function cover()
    {
        return $this->content()->cover()->toFile() ?? $this->parent()->cover()->toFile();
    }
}
?>