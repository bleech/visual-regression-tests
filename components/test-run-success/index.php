<?php
	$quotes = [
		[
			'quote' => __( 'It’s not what happens to you, but how you react to it that matters.', 'visual-regression-tests' ),
			'author' => 'Epictetus',
		],
		[
			'quote' => __( 'Change is inevitable. Progress is a choice.', 'visual-regression-tests' ),
			'author' => 'Tony Robbins',
		],
		[
			'quote' => __( 'Small changes can make a big difference. You are the only one who can make our world a better place to inhabit.', 'visual-regression-tests' ),
			'author' => 'Dr. Debasish Mridha',
		],
		[
			'quote' => __( 'It’s the little details that are vital. Little things make big things happen.', 'visual-regression-tests' ),
			'author' => 'John Wooden',
		],
		[
			'quote' => __( 'Excellence is in the details. Give attention to the details and excellence will come.', 'visual-regression-tests' ),
			'author' => 'Perry Paxton',
		],
		[
			'quote' => __( 'Attention to detail is not about perfection. It’s about excellence.', 'visual-regression-tests' ),
			'author' => 'Chris Denny',
		],
		[
			'quote' => __( 'The butterfly effect teaches us that small events can have very large effects.', 'visual-regression-tests' ),
			'author' => 'Edward Lorenz',
		],
		[
			'quote' => __( 'Not everything that is faced can be changed, but nothing can be changed until it is faced.', 'visual-regression-tests' ),
			'author' => 'James Baldwin',
		],
		[
			'quote' => __( 'Success is the sum of small efforts, repeated day in and day out.', 'visual-regression-tests' ),
			'author' => 'Robert Collier',
		],
		[
			'quote' => __( 'The details are not the details. They make the design.', 'visual-regression-tests' ),
			'author' => 'Charles Eames',
		],
		[
			'quote' => __( 'The devil is in the details.', 'visual-regression-tests' ),
			'author' => 'Johann Wolfgang von Goethe',
		],
		[
			'quote' => __( 'Details make perfection, and perfection is not a detail.', 'visual-regression-tests' ),
			'author' => 'Leonardo da Vinci',
		],
		[
			'quote' => __( 'The details are the key to success.', 'visual-regression-tests' ),
			'author' => 'Frank Lloyd Wright',
		],
		[
			'quote' => __( 'It’s not about the big things, it’s the small things that really matter.', 'visual-regression-tests' ),
			'author' => 'Steve Jobs',
		],
		[
			'quote' => __( 'Focus on the details and the big picture will take care of itself.', 'visual-regression-tests' ),
			'author' => 'Tom O’Toole',
		],
		[
			'quote' => __( 'The difference between something good and something great is attention to detail.', 'visual-regression-tests' ),
			'author' => 'Charles R. Swindoll',
		],
		[
			'quote' => __( 'Small things make a big difference.', 'visual-regression-tests' ),
			'author' => 'Yogi Berra',
		],
		[
			'quote' => __( 'Tiny tweaks can lead to big changes.', 'visual-regression-tests' ),
			'author' => 'Amy Cuddy',
		],
		[
			'quote' => __( 'Every success story is a tale of constant adaptation, revision, and change.', 'visual-regression-tests' ),
			'author' => 'Richard Branson',
		],
		[
			'quote' => __( 'A small change today makes a big difference tomorrow.', 'visual-regression-tests' ),
			'author' => 'Richard Bach',
		],
		[
			'quote' => __( 'Don’t fear failure. Fear being in the exact same place next year as you are today.', 'visual-regression-tests' ),
			'author' => 'Michael Hyatt',
		],
		[
			'quote' => __( 'Sometimes good things fall apart so better things can fall together.', 'visual-regression-tests' ),
			'author' => 'Marilyn Monroe',
		],
		[
			'quote' => __( 'Sometimes the smallest step in the right direction ends up being the biggest step of your life.', 'visual-regression-tests' ),
			'author' => 'Naeem Callaway',
		],
	];

	$random_quote = $quotes[ array_rand( $quotes ) ];

?>

<vrts-test-run-success class="vrts-test-run-success postbox">
	<div class="vrts-test-run-success__inner">
		<div class="vrts-test-run-success__lottie-player" vrts-lottie-player></div>
		<div class="vrts-test-run-success__content">
			<?php if ( $data['is_receipt'] ) : ?>
				<p><?php esc_html_e( 'Nice work – no more Alerts left to review!', 'visual-regression-tests' ); ?></p>
				<blockquote>
					<p>"<?php echo esc_html( $random_quote['quote'] ); ?>"</p>
					<cite>— <?php echo esc_html( $random_quote['author'] ); ?></cite>
				</blockquote>
			<?php else : ?>
				<p><?php esc_html_e( 'Smooth sailing – no changes found!', 'visual-regression-tests' ); ?></p>
				<p><?php esc_html_e( "You're good to go.", 'visual-regression-tests' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<span class="vrts-gradient-loader"></span>
</vrts-test-run-success>
