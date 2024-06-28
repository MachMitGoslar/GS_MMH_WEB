<?php
/*
  Snippets are a great way to store code snippets for reuse
  or to keep your templates clean.

  This header snippet is reused in all templates.
  It fetches information from the `site.txt` content file
  and contains the site navigation.

  More about snippets:
  https://getkirby.com/docs/guide/templates/snippets
*/
?>
<!DOCTYPE html>
<html lang="de">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
  <?php
  /*
    In the title tag we show the title of our
    site and the title of the current page
  */
  ?>
  <?php if($header_title = $slots->header_title): ?>
    <title><?= $site->title()->esc() ?> | <?= $header_title?></title>
  <?php else: ?>
    <title><?= $site->title()->esc() ?> | <?= $page->title()?></title>
  <?php endif;  ?>

  <?php
  /*
    Stylesheets can be included using the `css()` helper.
    Kirby also provides the `js()` helper to include script file.
    More Kirby helpers: https://getkirby.com/docs/reference/templates/helpers
  */
  ?>
  <?= css([
    'assets/css/prism.css',
    'assets/css/lightbox.css',
    'assets/css/index.css',
    '@auto'
  ]) ?>

  <?php
  /*
    The `url()` helper is a great way to create reliable
    absolute URLs in Kirby that always start with the
    base URL of your site.
  */
  ?>
  <link rel="shortcut icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
</head>
<body class="leading-normal tracking-normal text-white linear-gradient bg-gradient-to-r from-gold-700 to-gold-400"  >

  <header class="header ">
    <?php
    /*
      We use `$site->url()` to create a link back to the homepage
      for the logo and `$site->title()` as a temporary logo. You
      probably want to replace this with an SVG.
    */
    ?>


    <nav id="header" class="fixed w-full z-30 top-0 text-white">
      <div class="w-full container mx-auto flex flex-wrap items-center justify-between mt-0 py-2">
      <a class="" href="<?=$site->url()?>">
        <div class="pl-4 flex items-center toggleColourInv text-white no-underline hover:no-underline font-bold text-2xl lg:text-4xl">
          
          <div class="relative h-32 -ml-10 -mt-14 ">


              <svg class="absolute top-0 fill-white" id="black_layer">
                <use xlink:href="/assets/images/logo.svg#black_layer" />
              </svg>
              <svg class="absolute top-0 fill-black" id="white_layer">
                <use xlink:href="/assets/images/logo.svg#white_layer" />
              </svg>
              <svg class="absolute top-0 fill-gold">
                <use xlink:href="/assets/images/logo.svg#gold_layer" />
              </svg>
            </div>

        </div>
        </a>
        <div class="block lg:hidden pr-4">
          <button id="nav-toggle" data-collapse-toggle="nav-content" class="toggleColourInv flex items-center p-1 text-white hover:text-gray-900 focus:outline-none focus:shadow-outline transform transition hover:scale-105 duration-300 ease-in-out">
            <svg class="fill-current h-6 w-6" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <title>Menu</title>
              <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z" />
            </svg>
          </button>
        </div>
        <div class="w-full flex-grow lg:flex lg:items-center lg:w-auto hidden mt-2 lg:mt-0 bg-white lg:bg-transparent text-black p-4 lg:p-0 z-20" id="nav-content">
          <ul class="list-reset lg:flex justify-end flex-1 items-center">
            <?php foreach($site->children()->listed() as $nav_item): ?>
            <li class="mr-3">
              <a class="inline-block py-2 px-4 text-black font-bold no-underline" href="<?=$nav_item->url()?>"><?=$nav_item->title()->esc()?></a>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php
         /* <button
            id="navAction"
            class="mx-auto lg:mx-0 hover:underline bg-white text-gray-800 font-bold rounded-full mt-4 lg:mt-0 py-4 px-8 shadow opacity-75 focus:outline-none focus:shadow-outline transform transition hover:scale-105 duration-300 ease-in-out"
          >
            Action
          </button> */
          ?>
        </div>
      </div>
      <hr class="border-b border-gray-100 opacity-25 my-0 py-0" />
    </nav>

    <script>
      var scrollpos = window.scrollY;
      var header = document.getElementById("header");
      var navcontent = document.getElementById("nav-content");
      var brandname = document.getElementById("brandname");
      var toToggle = document.querySelectorAll(".toggleColour");
      var toToggle_inverse = document.querySelectorAll(".toggleColourInv");
      var whiteLayer =document.getElementById("white_layer");
      var blackLayer = document.getElementById("black_layer");


      document.addEventListener("scroll", function () {
        /*Apply classes for slide in bar*/
        scrollpos = window.scrollY;

        if (scrollpos > 10) {
          blackLayer.classList.add("fill-black");
          blackLayer.classList.remove("fill-white");
          whiteLayer.classList.add("fill-white");
          whiteLayer.classList.remove("black-layer");

          header.classList.add("bg-white");
          //Use to switch toggleColour colours
          for (var i = 0; i < toToggle.length; i++) {
            toToggle[i].classList.add("text-gray-800");
            toToggle[i].classList.remove("text-white");
          }
          header.classList.add("shadow");
          navcontent.classList.remove("bg-gray-100");
          navcontent.classList.add("bg-white");
        } else {
          header.classList.remove("bg-white");
          whiteLayer.classList.add("fill-black");
          whiteLayer.classList.remove("fill-white");
          blackLayer.classList.add("fill-white");
          blackLayer.classList.remove("black-layer");

          //Use to switch toggleColour colours
          for (var i = 0; i < toToggle.length; i++) {
            toToggle[i].classList.add("text-white");
            toToggle[i].classList.remove("text-gray-800");
          }

          //Use to switch toggleColour colours inverse
          for (var i = 0; i < toToggle_inverse.length; i++) {
            toToggle_inverse[i].classList.add("text-gray-800");
            toToggle_inverse[i].classList.remove("text-white");
          }

          header.classList.remove("shadow");
          navcontent.classList.remove("bg-white");
          navcontent.classList.add("bg-gray-100");
        }
      });
    </script>
    
  </header>

  <main class="pt-24">
    <?php if($hero = $slots->hero()): ?>
      <?= $hero ?>
    <?php endif;  ?>

    <?php snippet('divider_top')  ?>

    <section class=" bg-white py-3">
      <div class="container px-3 mx-auto flex flex-wrap flex-col md:flex-row items-center">
        <?= $slot ?>
      </div>
    </section>
    <?php snippet('divider_bottom') ?>

  </main>

  <?php snippet('footer') ?>


      