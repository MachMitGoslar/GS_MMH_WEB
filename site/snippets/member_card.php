<?php 
    $member = $member;
    if(!isset($short)) {
        $short = false;
    }
?>
<div class="max-w-4xl flex items-stretch h-auto flex-wrap mx-auto my-32 <?= $short ? '' : 'lg:my-0 lg:h-max '?>">

	<!--Main Col-->
	<div id="profile"
		class="w-full <?= $short ? '' : 'lg:w-3/5 lg:rounded-l-lg lg:rounded-r-none lg:mx-0' ?> rounded-lg  shadow-2xl bg-white opacity-75 mx-6 ">


		<div class="p-4 md:p-12 text-center <?= $short ? '' : 'lg:text-left'?>">
			<!-- Image for mobile view-->
			<div class="block <?= $short ? '' : 'lg:hidden' ?> rounded-full shadow-xl mx-auto -mt-16 h-48 w-48 bg-cover bg-center"
				style="background-image: url('<?= $member->cover()->crop(200,200)->url() ?>')"></div>

			<h1 class="text-3xl font-bold pt-8 <?= $short ? '' : 'lg:pt-0'?>"><?= $member->name() ?></h1>
			<div class="mx-auto <?= $short ? '' : 'lg:mx-0'?> w-4/5 pt-3 border-b-2 border-gold-500 opacity-25"></div>
			<?php if ($member->role()->isNotEmpty()): ?>
				<p class="pt-4 format text-base font-bold flex items-center justify-center <?= $short ? '' : 'lg:justify-start'?>">
					<svg class="h-4 fill-current text-gold-700 pr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
						<path d="M9 12H1v6a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-6h-8v2H9v-2zm0-1H0V5c0-1.1.9-2 2-2h4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1h4a2 2 0 0 1 2 2v6h-9V9H9v2zm3-8V2H8v1h4z" />
					</svg> 
					<?= $member->role()->esc() ?>
				</p> 
			<?php endif ?>
			<?php if ($member->address()->isNotEmpty()): ?>
				<p class="pt-2 text-gray-600 text-xs flex items-center justify-center <?= $short ? '' : 'lg:justify-start lg:text-sm'?>">
					<svg class="h-4 fill-current text-gold-700 pr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
						<path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm7.75-8a8.01 8.01 0 0 0 0-4h-3.82a28.81 28.81 0 0 1 0 4h3.82zm-.82 2h-3.22a14.44 14.44 0 0 1-.95 3.51A8.03 8.03 0 0 0 16.93 14zm-8.85-2h3.84a24.61 24.61 0 0 0 0-4H8.08a24.61 24.61 0 0 0 0 4zm.25 2c.41 2.4 1.13 4 1.67 4s1.26-1.6 1.67-4H8.33zm-6.08-2h3.82a28.81 28.81 0 0 1 0-4H2.25a8.01 8.01 0 0 0 0 4zm.82 2a8.03 8.03 0 0 0 4.17 3.51c-.42-.96-.74-2.16-.95-3.51H3.07zm13.86-8a8.03 8.03 0 0 0-4.17-3.51c.42.96.74 2.16.95 3.51h3.22zm-8.6 0h3.34c-.41-2.4-1.13-4-1.67-4S8.74 3.6 8.33 6zM3.07 6h3.22c.2-1.35.53-2.55.95-3.51A8.03 8.03 0 0 0 3.07 6z" />
					</svg> 
					<?= $member->address()->esc() ?>
				</p>
			<?php endif ?>
			
			<?php if($member->phone()->isNotEmpty()): ?>
			<p class="pt-2 text-gray-600 text-xs flex items-center justify-center <?= $short ? '' : 'lg:justify-start lg:text-sm'?>">

			<svg class="h-4 fill-current text-gold-700 pr-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
				y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
			<g>
				<path d="M34.9,9.5h-5.8c-1.2,0-2.3,1-2.3,2.3s1,2.3,2.3,2.3h5.8c1.2,0,2.3-1,2.3-2.3S36.1,9.5,34.9,9.5z"/>
				<path d="M44.4,1.8H19.6c-3.4,0-6.3,2.8-6.3,6.3v48c0,3.4,2.8,6.3,6.3,6.3h24.9c3.4,0,6.3-2.8,6.3-6.3V8C50.7,4.6,47.9,1.8,44.4,1.8
					z M46.2,56c0,1-0.8,1.8-1.8,1.8H19.6c-1,0-1.8-0.8-1.8-1.8V8c0-1,0.8-1.8,1.8-1.8h24.9c1,0,1.8,0.8,1.8,1.8V56z"/>
				<path d="M34.1,53.3C34,53.2,34,53.1,33.9,53c-0.1-0.1-0.2-0.2-0.3-0.3c-0.5-0.5-1.3-0.8-2-0.6c-0.1,0-0.3,0.1-0.4,0.1
					c-0.1,0-0.3,0.1-0.4,0.2c-0.1,0.1-0.2,0.2-0.3,0.3c-0.1,0.1-0.2,0.2-0.3,0.3c-0.1,0.1-0.1,0.3-0.2,0.4c-0.1,0.1-0.1,0.3-0.1,0.4
					c0,0.1,0,0.3,0,0.4c0,0.6,0.2,1.2,0.7,1.6c0.1,0.1,0.2,0.2,0.3,0.3c0.1,0.1,0.3,0.2,0.4,0.2c0.1,0,0.3,0.1,0.4,0.1
					c0.1,0,0.3,0,0.4,0c0.6,0,1.2-0.2,1.6-0.7c0.1-0.1,0.2-0.2,0.3-0.3c0.1-0.1,0.1-0.3,0.2-0.4c0.1-0.1,0.1-0.3,0.1-0.4
					c0-0.2,0-0.3,0-0.4c0-0.2,0-0.3,0-0.4C34.2,53.6,34.1,53.5,34.1,53.3z"/>
			</g>
			</svg>
				<?= HTML::tel($member->phone()->esc()) ?>
		</p>
		<?php endif ?>

			<p class="pt-8 text-sm format"><?= $short ? $member->description()->excerpt(100) : $member->description() ?></p>

            <?php if($short): ?>
 			<div class="pt-12 pb-8">
				<a href="<?=$member->url()?>" class="bg-gold-700 hover:bg-green-900 text-white font-bold py-2 px-4 rounded-full">
					Mehr anzeigen
				</a>
			</div>           
            <?php else: ?>
			<div class="pt-12 pb-8">
				<a href="mailto:<?=$member->email()?>" class="bg-gold-700 hover:bg-green-900 text-white font-bold py-2 px-4 rounded-full">
					Schreib mir
			</a>
			</div>
            <?php endif ?>
			<?php if ($member->teams()->exists() && !$short): ?>
			<p class="text-sm font-bold"> Teams </p>
			<div class="mt-2 pb-16 lg:pb-0 w-4/5 lg:w-full mx-auto flex flex-wrap items-center justify-start">
				<?php foreach($member->teams()->split() as $team): ?>
					<span class="bg-gold-100 text-gold-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gold-900 dark:text-gold-300">#<?= $team ?></span>
				<?php endforeach ?>
			</div>
			<?php endif ?>
            <?php if(!$short):?>
			<div class="mt-6 pb-16 lg:pb-0 w-4/5 lg:w-full mx-auto flex flex-wrap items-center justify-between">
				<?php if($member->facebook()->exists()): ?>
				<a class="link" href="https://www.facebook.com/<?=$member->facebook()?>" data-tippy-content="@facebook_handle"><svg
						class="h-6 fill-current text-gray-600 hover:text-gold-700" role="img" viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg">
						<title>Facebook</title>
						<path
							d="M22.676 0H1.324C.593 0 0 .593 0 1.324v21.352C0 23.408.593 24 1.324 24h11.494v-9.294H9.689v-3.621h3.129V8.41c0-3.099 1.894-4.785 4.659-4.785 1.325 0 2.464.097 2.796.141v3.24h-1.921c-1.5 0-1.792.721-1.792 1.771v2.311h3.584l-.465 3.63H16.56V24h6.115c.733 0 1.325-.592 1.325-1.324V1.324C24 .593 23.408 0 22.676 0" />
					</svg></a>
				<?php endif ?>
				<?php if($member->x()->exists()): ?>
				<a class="link" href="https://x.com/<?=$member->x()?>" data-tippy-content="@twitter_handle"><svg
						class="h-7 fill-current text-gray-600 hover:text-gold-700" role="img" viewBox="0 0 50 50"
						xmlns="http://www.w3.org/2000/svg">
						<title>X</title>
						<path xmlns="http://www.w3.org/2000/svg" d="M 11 4 C 7.134 4 4 7.134 4 11 L 4 39 C 4 42.866 7.134 46 11 46 L 39 46 C 42.866 46 46 42.866 46 39 L 46 11 C 46 7.134 42.866 4 39 4 L 11 4 z M 13.085938 13 L 21.023438 13 L 26.660156 21.009766 L 33.5 13 L 36 13 L 27.789062 22.613281 L 37.914062 37 L 29.978516 37 L 23.4375 27.707031 L 15.5 37 L 13 37 L 22.308594 26.103516 L 13.085938 13 z M 16.914062 15 L 31.021484 35 L 34.085938 35 L 19.978516 15 L 16.914062 15 z"/>
					</svg></a>
				<?php endif ?>
				<?php if($member->linkedin()->exists()): ?>
					<a class="link" href="https://de.linkedin.com/in/<?=$member->linkedin()?>" data-tippy-content="@linked_handle">
						<svg class="h-9 fill-current text-gray-600 hover:text-gold-700" role="img" viewBox="0 0 24 30"
						xmlns="http://www.w3.org/2000/svg">
						<title>LinkedIn</title>
						<path xmlns="http://www.w3.org/2000/svg" d="M0 8.219v15.563c0 1.469 1.156 2.625 2.625 2.625h15.563c0.719 0 1.406-0.344 1.844-0.781 0.469-0.469 0.781-1.063 0.781-1.844v-15.563c0-1.469-1.156-2.625-2.625-2.625h-15.563c-0.781 0-1.375 0.313-1.844 0.781-0.438 0.438-0.781 1.125-0.781 1.844zM2.813 10.281c0-1 0.813-1.875 1.813-1.875 1.031 0 1.875 0.875 1.875 1.875 0 1.031-0.844 1.844-1.875 1.844-1 0-1.813-0.813-1.813-1.844zM7.844 23.125v-9.531c0-0.219 0.219-0.406 0.375-0.406h2.656c0.375 0 0.375 0.438 0.375 0.719 0.75-0.75 1.719-0.938 2.719-0.938 2.438 0 4 1.156 4 3.719v6.438c0 0.219-0.188 0.406-0.375 0.406h-2.75c-0.219 0-0.375-0.219-0.375-0.406v-5.813c0-0.969-0.281-1.5-1.375-1.5-1.375 0-1.719 0.906-1.719 2.125v5.188c0 0.219-0.219 0.406-0.438 0.406h-2.719c-0.156 0-0.375-0.219-0.375-0.406zM2.875 23.125v-9.531c0-0.219 0.219-0.406 0.375-0.406h2.719c0.25 0 0.406 0.156 0.406 0.406v9.531c0 0.219-0.188 0.406-0.406 0.406h-2.719c-0.188 0-0.375-0.219-0.375-0.406z"/>
					</svg></a>
				<?php endif ?>
				<?php if($member->github()->exists()): ?>
				<a class="link" href="https://github.com/<?=$member->github()?>" data-tippy-content="@github_handle"><svg
						class="h-6 fill-current text-gray-600 hover:text-gold-700" role="img" viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg">
						<title>GitHub</title>
						<path
							d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
					</svg></a>
				<?php endif ?>
				<?php if($member->instagram()->exists()): ?>
				<a class="link" href="https://www.instagram.com/<?=$member->instagram()?>" data-tippy-content="@instagram_handle"><svg
						class="h-6 fill-current text-gray-600 hover:text-gold-700" role="img" viewBox="0 0 24 24"
						xmlns="http://www.w3.org/2000/svg">
						<title>Instagram</title>
						<path
							d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
					</svg></a>
				<?php endif ?>
				<?php if($member->youtube()->exists()): ?>
				<a class="link" href="https://www.youtube.com/<?=$member->youtube()?>" data-tippy-content="@youtube_handle"><svg
						class="h-9 fill-current text-gray-600 hover:text-gold-700" role="img"
						xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
						<title>YouTube</title>
						<path xmlns="http://www.w3.org/2000/svg" d="M 44.898438 14.5 C 44.5 12.300781 42.601563 10.699219 40.398438 10.199219 C 37.101563 9.5 31 9 24.398438 9 C 17.800781 9 11.601563 9.5 8.300781 10.199219 C 6.101563 10.699219 4.199219 12.199219 3.800781 14.5 C 3.398438 17 3 20.5 3 25 C 3 29.5 3.398438 33 3.898438 35.5 C 4.300781 37.699219 6.199219 39.300781 8.398438 39.800781 C 11.898438 40.5 17.898438 41 24.5 41 C 31.101563 41 37.101563 40.5 40.601563 39.800781 C 42.800781 39.300781 44.699219 37.800781 45.101563 35.5 C 45.5 33 46 29.398438 46.101563 25 C 45.898438 20.5 45.398438 17 44.898438 14.5 Z M 19 32 L 19 18 L 31.199219 25 Z"/>
					</svg></a>
				<?php endif ?>
			</div>
            <?php endif ?>

			<!-- Use https://simpleicons.org/ to find the svg for your preferred product -->

		</div>

	</div>

    <?php if(!$short): ?>
	<!--Img Col-->
	<div class="w-full lg:w-2/5">
		<!-- Big profile image for side bar (desktop) -->
		<img src="<?=$member->cover()->url()?>"
			class="rounded-none lg:rounded-lg shadow-2xl hidden lg:block">
		<!-- Image from: http://unsplash.com/photos/MP0IUfwrn0A -->

	</div>
    <?php endif ?>

</div>