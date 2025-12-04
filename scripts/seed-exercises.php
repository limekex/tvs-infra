<?php
/**
 * Seed Exercise Library
 *
 * Populates tvs_exercise CPT with common exercises.
 * Run via: make wp cmd="eval-file /scripts/seed-exercises.php"
 *
 * @package TVS_Virtual_Sports
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "Error: This script must be run within WordPress context.\n";
	exit( 1 );
}

echo "🏋️  Starting Exercise Library Seed...\n\n";

// Define exercise categories (taxonomy terms)
$categories = array(
	'legs'      => 'Legs',
	'chest'     => 'Chest',
	'back'      => 'Back',
	'shoulders' => 'Shoulders',
	'arms'      => 'Arms',
	'core'      => 'Core',
	'fullbody'  => 'Full Body',
);

// Define exercise types (taxonomy terms)
$types = array(
	'strength'     => 'Strength',
	'cardio'       => 'Cardio',
	'flexibility'  => 'Flexibility',
	'balance'      => 'Balance',
);

// Create/ensure taxonomies exist
echo "Creating taxonomies...\n";
foreach ( $categories as $slug => $name ) {
	if ( ! term_exists( $slug, 'exercise_category' ) ) {
		wp_insert_term( $name, 'exercise_category', array( 'slug' => $slug ) );
		echo "  ✓ Category: {$name}\n";
	}
}
foreach ( $types as $slug => $name ) {
	if ( ! term_exists( $slug, 'exercise_type' ) ) {
		wp_insert_term( $name, 'exercise_type', array( 'slug' => $slug ) );
		echo "  ✓ Type: {$name}\n";
	}
}

echo "\n";

// Define exercises
$exercises = array(
	// LEGS
	array(
		'name'          => 'Barbell Back Squat',
		'description'   => 'Compound lower body exercise targeting quads, glutes, and hamstrings. Place barbell on upper back, squat down keeping chest up and knees tracking over toes.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'barbell', 'rack' ),
		'muscles'       => array( 'quads', 'glutes', 'hamstrings', 'core' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Front Squat',
		'description'   => 'Squat variation with barbell held at shoulder height. Emphasizes quads and requires good mobility.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'barbell', 'rack' ),
		'muscles'       => array( 'quads', 'glutes', 'core' ),
		'difficulty'    => 'advanced',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Leg Press',
		'description'   => 'Machine-based leg exercise. Great for building quad and glute strength with reduced spinal load.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'machine' ),
		'muscles'       => array( 'quads', 'glutes', 'hamstrings' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Romanian Deadlift',
		'description'   => 'Hip hinge movement targeting hamstrings and glutes. Keep slight knee bend and lower bar along shins.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'barbell' ),
		'muscles'       => array( 'hamstrings', 'glutes', 'back' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Walking Lunges',
		'description'   => 'Dynamic single-leg exercise. Step forward and lower back knee toward ground, alternating legs.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell', 'bodyweight' ),
		'muscles'       => array( 'quads', 'glutes', 'hamstrings' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Bulgarian Split Squat',
		'description'   => 'Single-leg squat with rear foot elevated. Excellent for balance and unilateral leg strength.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell', 'bench' ),
		'muscles'       => array( 'quads', 'glutes', 'hamstrings' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Calf Raises',
		'description'   => 'Isolation exercise for calf muscles. Can be done standing or seated.',
		'category'      => 'legs',
		'type'          => 'strength',
		'equipment'     => array( 'machine', 'dumbbell', 'bodyweight' ),
		'muscles'       => array( 'calves' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),

	// CHEST
	array(
		'name'          => 'Barbell Bench Press',
		'description'   => 'Classic chest builder. Lie on bench, lower bar to chest, press up. Keep shoulder blades retracted.',
		'category'      => 'chest',
		'type'          => 'strength',
		'equipment'     => array( 'barbell', 'bench' ),
		'muscles'       => array( 'chest', 'shoulders', 'triceps' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Dumbbell Bench Press',
		'description'   => 'Bench press with dumbbells. Allows greater range of motion and independent arm movement.',
		'category'      => 'chest',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell', 'bench' ),
		'muscles'       => array( 'chest', 'shoulders', 'triceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Incline Dumbbell Press',
		'description'   => 'Upper chest focus. Bench set to 30-45 degrees, press dumbbells overhead.',
		'category'      => 'chest',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell', 'bench' ),
		'muscles'       => array( 'chest', 'shoulders', 'triceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Push-ups',
		'description'   => 'Bodyweight chest exercise. Hands slightly wider than shoulders, lower chest to ground.',
		'category'      => 'chest',
		'type'          => 'strength',
		'equipment'     => array( 'bodyweight' ),
		'muscles'       => array( 'chest', 'shoulders', 'triceps', 'core' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Cable Flyes',
		'description'   => 'Isolation movement for chest. Keep slight elbow bend and bring hands together in arc motion.',
		'category'      => 'chest',
		'type'          => 'strength',
		'equipment'     => array( 'cable' ),
		'muscles'       => array( 'chest' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),

	// BACK
	array(
		'name'          => 'Deadlift',
		'description'   => 'King of back exercises. Hip hinge to lift barbell from floor. Engages entire posterior chain.',
		'category'      => 'back',
		'type'          => 'strength',
		'equipment'     => array( 'barbell' ),
		'muscles'       => array( 'back', 'glutes', 'hamstrings', 'core' ),
		'difficulty'    => 'advanced',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Pull-ups',
		'description'   => 'Bodyweight back exercise. Hang from bar, pull chin above bar. Great for lats and biceps.',
		'category'      => 'back',
		'type'          => 'strength',
		'equipment'     => array( 'pullup_bar' ),
		'muscles'       => array( 'back', 'biceps' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Barbell Row',
		'description'   => 'Bent-over row. Hinge at hips, pull bar to lower chest. Builds thick back.',
		'category'      => 'back',
		'type'          => 'strength',
		'equipment'     => array( 'barbell' ),
		'muscles'       => array( 'back', 'biceps', 'core' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Lat Pulldown',
		'description'   => 'Machine alternative to pull-ups. Pull bar down to chest, squeeze shoulder blades.',
		'category'      => 'back',
		'type'          => 'strength',
		'equipment'     => array( 'cable', 'machine' ),
		'muscles'       => array( 'back', 'biceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Seated Cable Row',
		'description'   => 'Pull handle to torso, squeezing shoulder blades together. Mid-back builder.',
		'category'      => 'back',
		'type'          => 'strength',
		'equipment'     => array( 'cable' ),
		'muscles'       => array( 'back', 'biceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),

	// SHOULDERS
	array(
		'name'          => 'Overhead Press',
		'description'   => 'Standing barbell press. Press barbell from shoulders to overhead, keeping core tight.',
		'category'      => 'shoulders',
		'type'          => 'strength',
		'equipment'     => array( 'barbell' ),
		'muscles'       => array( 'shoulders', 'triceps', 'core' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Dumbbell Shoulder Press',
		'description'   => 'Seated or standing dumbbell press. Press dumbbells overhead from shoulder height.',
		'category'      => 'shoulders',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell', 'bench' ),
		'muscles'       => array( 'shoulders', 'triceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Lateral Raises',
		'description'   => 'Isolation for side delts. Raise dumbbells out to sides until parallel to ground.',
		'category'      => 'shoulders',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell' ),
		'muscles'       => array( 'shoulders' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Face Pulls',
		'description'   => 'Cable exercise for rear delts and upper back. Pull rope toward face, elbows high.',
		'category'      => 'shoulders',
		'type'          => 'strength',
		'equipment'     => array( 'cable' ),
		'muscles'       => array( 'shoulders', 'back' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),

	// ARMS
	array(
		'name'          => 'Barbell Curl',
		'description'   => 'Classic bicep builder. Curl bar from thighs to shoulders, keeping elbows stationary.',
		'category'      => 'arms',
		'type'          => 'strength',
		'equipment'     => array( 'barbell' ),
		'muscles'       => array( 'biceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Dumbbell Hammer Curl',
		'description'   => 'Neutral grip curl. Targets biceps and forearms. Palms face each other throughout.',
		'category'      => 'arms',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell' ),
		'muscles'       => array( 'biceps', 'forearms' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Tricep Dips',
		'description'   => 'Bodyweight tricep exercise. Lower body by bending elbows, press back up.',
		'category'      => 'arms',
		'type'          => 'strength',
		'equipment'     => array( 'bodyweight', 'bench' ),
		'muscles'       => array( 'triceps', 'chest', 'shoulders' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Overhead Tricep Extension',
		'description'   => 'Isolation for triceps. Hold dumbbell overhead, lower behind head, extend back up.',
		'category'      => 'arms',
		'type'          => 'strength',
		'equipment'     => array( 'dumbbell' ),
		'muscles'       => array( 'triceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Cable Tricep Pushdown',
		'description'   => 'Cable isolation for triceps. Push handle down, keeping elbows at sides.',
		'category'      => 'arms',
		'type'          => 'strength',
		'equipment'     => array( 'cable' ),
		'muscles'       => array( 'triceps' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'reps',
	),

	// CORE
	array(
		'name'          => 'Plank',
		'description'   => 'Isometric core exercise. Hold push-up position on forearms, keeping body straight.',
		'category'      => 'core',
		'type'          => 'strength',
		'equipment'     => array( 'bodyweight' ),
		'muscles'       => array( 'core' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'time',
	),
	array(
		'name'          => 'Hanging Leg Raises',
		'description'   => 'Advanced ab exercise. Hang from bar, raise legs to 90 degrees.',
		'category'      => 'core',
		'type'          => 'strength',
		'equipment'     => array( 'pullup_bar' ),
		'muscles'       => array( 'core' ),
		'difficulty'    => 'advanced',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Russian Twists',
		'description'   => 'Rotational core exercise. Sit with feet elevated, twist torso side to side.',
		'category'      => 'core',
		'type'          => 'strength',
		'equipment'     => array( 'bodyweight', 'dumbbell' ),
		'muscles'       => array( 'core' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Ab Wheel Rollout',
		'description'   => 'Advanced core exercise. Roll wheel forward, keeping core tight, then pull back.',
		'category'      => 'core',
		'type'          => 'strength',
		'equipment'     => array( 'bodyweight' ),
		'muscles'       => array( 'core', 'shoulders' ),
		'difficulty'    => 'advanced',
		'default_metric'=> 'reps',
	),

	// FULL BODY
	array(
		'name'          => 'Burpees',
		'description'   => 'Full body conditioning. Drop to push-up, jump feet forward, jump up with hands overhead.',
		'category'      => 'fullbody',
		'type'          => 'cardio',
		'equipment'     => array( 'bodyweight' ),
		'muscles'       => array( 'chest', 'core', 'quads', 'shoulders' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Kettlebell Swings',
		'description'   => 'Explosive hip hinge. Swing kettlebell from between legs to chest height.',
		'category'      => 'fullbody',
		'type'          => 'cardio',
		'equipment'     => array( 'kettlebell' ),
		'muscles'       => array( 'glutes', 'hamstrings', 'core', 'shoulders' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Box Jumps',
		'description'   => 'Explosive lower body power. Jump onto elevated platform, step down.',
		'category'      => 'fullbody',
		'type'          => 'cardio',
		'equipment'     => array( 'box' ),
		'muscles'       => array( 'quads', 'glutes', 'calves' ),
		'difficulty'    => 'intermediate',
		'default_metric'=> 'reps',
	),
	array(
		'name'          => 'Mountain Climbers',
		'description'   => 'Cardio core exercise. In plank position, drive knees to chest alternating rapidly.',
		'category'      => 'fullbody',
		'type'          => 'cardio',
		'equipment'     => array( 'bodyweight' ),
		'muscles'       => array( 'core', 'shoulders', 'quads' ),
		'difficulty'    => 'beginner',
		'default_metric'=> 'time',
	),
);

// Insert exercises
$created = 0;
$skipped = 0;

echo "Inserting exercises...\n";

foreach ( $exercises as $exercise ) {
	// Check if exercise already exists
	$existing = get_posts( array(
		'post_type'   => 'tvs_exercise',
		'post_status' => 'any',
		'title'       => $exercise['name'],
		'numberposts' => 1,
	) );

	if ( ! empty( $existing ) ) {
		echo "  ⊘ Skipped: {$exercise['name']} (already exists)\n";
		$skipped++;
		continue;
	}

	// Create exercise post
	$post_id = wp_insert_post( array(
		'post_type'    => 'tvs_exercise',
		'post_title'   => $exercise['name'],
		'post_content' => $exercise['description'],
		'post_status'  => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		echo "  ✗ Error creating: {$exercise['name']}\n";
		continue;
	}

	// Set taxonomies
	wp_set_object_terms( $post_id, $exercise['category'], 'exercise_category' );
	wp_set_object_terms( $post_id, $exercise['type'], 'exercise_type' );

	// Set meta
	update_post_meta( $post_id, '_tvs_equipment', $exercise['equipment'] );
	update_post_meta( $post_id, '_tvs_muscle_groups', $exercise['muscles'] );
	update_post_meta( $post_id, '_tvs_difficulty', $exercise['difficulty'] );
	update_post_meta( $post_id, '_tvs_default_metric_type', $exercise['default_metric'] );

	echo "  ✓ Created: {$exercise['name']} (ID: {$post_id})\n";
	$created++;
}

echo "\n";
echo "✅ Seed complete!\n";
echo "   Created: {$created} exercises\n";
echo "   Skipped: {$skipped} exercises (already existed)\n";
echo "\n";
echo "🔍 View exercises in WP Admin → TVS → Exercise Library\n";
echo "🧪 Test API: /wp-json/tvs/v1/exercises/search?q=squat\n";
