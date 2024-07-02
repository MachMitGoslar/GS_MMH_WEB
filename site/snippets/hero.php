<?php 

/**************
   @var string $title 
   @var string $subtitle
   @var image $cover
   @var link_object $cta 
**************/
?>

<div class="container px-3 mx-auto flex flex-wrap flex-col md:flex-row items-center">
    <!--Left Col-->
    <div class="flex flex-col w-full z-10 md:w-1/2 justify-center items-start text-center md:text-left">
        <p class="uppercase tracking-loose w-full">Was wir so machen?</p>
        <h1 class="my-4 text-3xl md:text-5xl font-bold leading-tight">
            <?= $title ?>
        </h1>
        <?php if(isset($subheading) && $subheading->isNotEmpty()): ?>
        <p class="leading-normal text-2xl mb-8">
            <?=$subheading?>
        </p>
        <?php endif ?>
        <?php if(isset($cta) && $cta->isNotEmpty()): ?>
        <button href="<?=$cta->link()?>"class="mx-auto lg:mx-0 hover:underline bg-white text-gray-800 font-bold rounded-full my-6 py-4 px-8 shadow-lg focus:outline-none focus:shadow-outline transform transition hover:scale-105 duration-300 ease-in-out">
            <?= $cta->linkText()?>
        </button>
        <?php endif ?>
    </div>
    <!--Right Col-->
    <div class="w-full md:w-1/2 py-6 text-center">
        <img class="w-full md:w-4/5 z-50" src="<?=$cover->url()?>" />
    </div>
    </div>
</div>