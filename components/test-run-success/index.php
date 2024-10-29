<?php
	$quotes = [
		[
			'quote' => __( 'Change is inevitable – except from a vending machine.', 'visual-regression-tests' ),
			'author' => 'Robert C. Gallagher',
		],
		[
			'quote' => __( 'It’s not what happens to you, but how you react to it that matters.', 'visual-regression-tests' ),
			'author' => 'Epictetus',
		],
		[
			'quote' => __( 'Every success story is a tale of constant adaptation, revision, and change.', 'visual-regression-tests' ),
			'author' => 'Richard Branson',
		],
		[
			'quote' => __( 'Change is hard at first, messy in the middle and gorgeous at the end.', 'visual-regression-tests' ),
			'author' => 'Robin Sharma',
		],
		[
			'quote' => __( 'Without deviation from the norm, progress is not possible.', 'visual-regression-tests' ),
			'author' => 'Frank Zappa',
		],
		[
			'quote' => __( 'Perfection is not attainable, but if we chase perfection we can catch excellence.', 'visual-regression-tests' ),
			'author' => 'Vince Lombardi',
		],
		[
			'quote' => __( 'It’s the little details that are vital. Little things make big things happen.', 'visual-regression-tests' ),
			'author' => 'John Wooden',
		],
		[
			'quote' => __( 'It is not the strongest of the species that survive, nor the most intelligent. It is the one that is most adaptable to change.', 'visual-regression-tests' ),
			'author' => 'Charles Darwin',
		],
		[
			'quote' => __( 'Attention to detail is not about perfection. It’s about excellence, about constant improvement.', 'visual-regression-tests' ),
			'author' => 'Chris Denny',
		],
		[
			'quote' => __( 'The difference between something good and something great is attention to detail.', 'visual-regression-tests' ),
			'author' => 'Charles R Swindoll',
		],
		[
			'quote' => __( 'Details make perfection, and perfection is not a detail.', 'visual-regression-tests' ),
			'author' => 'Leonardo da Vinci',
		],
		[
			'quote' => __( 'Not everything that is faced can be changed, but nothing can be changed until it is faced.', 'visual-regression-tests' ),
			'author' => 'James Baldwin',
		],
		[
			'quote' => __( 'Small deeds done are better than great deeds planned.', 'visual-regression-tests' ),
			'author' => 'Peter Marshall',
		],
		[
			'quote' => __( 'Great things are not done by impulse, but by a series of small things brought together.', 'visual-regression-tests' ),
			'author' => 'Vincent Van Gogh',
		],
		[
			'quote' => __( 'Success is the sum of small efforts, repeated day in and day out.', 'visual-regression-tests' ),
			'author' => 'Robert Collier',
		],
		[
			'quote' => __( 'To improve is to change; to be perfect is to change often.', 'visual-regression-tests' ),
			'author' => 'Winston Churchill',
		],
		[
			'quote' => __( 'The details are not the details. They make the design.', 'visual-regression-tests' ),
			'author' => 'Charles Eames',
		],
		[
			'quote' => __( 'God is in the details.', 'visual-regression-tests' ),
			'author' => 'Ludwig Mies van der Rohe',
		],
		[
			'quote' => __( 'Excellence is the gradual result of always striving to do better.', 'visual-regression-tests' ),
			'author' => 'Pat Riley',
		],
		[
			'quote' => __( 'The world is changed by your example, not by your opinion.', 'visual-regression-tests' ),
			'author' => 'Paulo Coelho',
		],
		[
			'quote' => __( 'Doing the little things can make a big difference.', 'visual-regression-tests' ),
			'author' => 'Yogi Berra',
		],
		[
			'quote' => __( 'Tiny tweaks can lead to big changes.', 'visual-regression-tests' ),
			'author' => 'Amy Cuddy',
		],
		[
			'quote' => __( 'Life is a series of natural and spontaneous changes. Don’t resist them—that only creates sorrow. Let reality be reality.', 'visual-regression-tests' ),
			'author' => 'Lao Tzu',
		],
		[
			'quote' => __( 'A bend in the road is not the end of the road… unless you fail to make the turn.', 'visual-regression-tests' ),
			'author' => 'Helen Keller',
		],
		[
			'quote' => __( 'The only way to make sense out of change is to plunge into it, move with it, and join the dance.', 'visual-regression-tests' ),
			'author' => 'Alan Watts',
		],
		[
			'quote' => __( 'Change is the only constant in life.', 'visual-regression-tests' ),
			'author' => 'Heraclitus',
		],
		[
			'quote' => __( 'The art of life lies in a constant readjustment to our surroundings.', 'visual-regression-tests' ),
			'author' => 'Okakura Kakuzō',
		],
		[
			'quote' => __( 'Adaptability is about the powerful difference between adapting to cope and adapting to win.', 'visual-regression-tests' ),
			'author' => 'Max McKeown',
		],
		[
			'quote' => __( 'The pessimist complains about the wind; the optimist expects it to change; the realist adjusts the sails.', 'visual-regression-tests' ),
			'author' => 'William Arthur Ward',
		],
		[
			'quote' => __( 'The world hates change, yet it is the only thing that has brought progress.', 'visual-regression-tests' ),
			'author' => 'Charles Kettering',
		],
		[
			'quote' => __( 'Change is inevitable. Change is constant.', 'visual-regression-tests' ),
			'author' => 'Benjamin Disraeli',
		],
		[
			'quote' => __( 'You must welcome change as the rule but not as your ruler.', 'visual-regression-tests' ),
			'author' => 'Denis Waitley',
		],
		[
			'quote' => __( 'Change is the process by which the future invades our lives.', 'visual-regression-tests' ),
			'author' => 'Alvin Toffler',
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
			'quote' => __( 'Change starts when someone sees the next step.', 'visual-regression-tests' ),
			'author' => 'William Drayton',
		],
	];

	mt_srand( $data['run']->id );
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
