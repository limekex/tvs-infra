# Exercise Library & Circuit Grouping - Feature Spec

## Overview
Enhance manual activity tracker with exercise library, search functionality, and circuit grouping for comprehensive workout tracking.

## Current State (v1.0 - Implemented)
✅ Per-exercise tracking with individual sets/reps/weight
✅ Free-text exercise names
✅ Total volume and reps calculation
✅ Backend storage as JSON array

## Proposed Enhancements

### Phase 1: Exercise Library CPT (Recommended Next)

```php
Fields:
- post_title: Exercise name (e.g., "Barbell Back Squat")
- post_content: Description/instructions
- taxonomy: exercise_category (Legs, Chest, Back, Shoulders, Arms, Core, Full Body)
- taxonomy: exercise_type (Strength, Cardio, Flexibility, Balance)
- meta: equipment_required (array: Barbell, Dumbbells, Bodyweight, etc.)
- meta: muscle_groups (array: Quads, Glutes, Hamstrings, etc.)
- meta: difficulty (Beginner, Intermediate, Advanced)
- meta: video_url (optional YouTube/Vimeo embed)
- meta: animation_url (optional GIF/video for demonstration)
- meta: default_metric_type (reps, time, distance)
```

#### Benefits:
- ✅ Consistent naming across users
- ✅ Exercise descriptions and form tips
- ✅ Future: Attach video demonstrations
- ✅ Future: Track personal records per exercise
- ✅ Future: Exercise recommendations based on workout type
- ✅ Search and autocomplete
- ✅ Admin can curate quality exercise library

#### UI Changes:
```javascript
// Autocomplete/search combo:
<Combobox>
  <input 
    type="text" 
    placeholder="Search exercises or add custom..." 
    onChange={handleSearchExercises}
  />
  {searchResults.length > 0 && (
    <ul className="tvs-exercise-dropdown">
      {searchResults.map(exercise => (
        <li onClick={() => selectExercise(exercise)}>
          <strong>{exercise.name}</strong>
          <span className="tvs-exercise-category">{exercise.category}</span>
        </li>
      ))}
      <li className="tvs-exercise-custom">
        + Add "{searchQuery}" as custom exercise
      </li>
    </ul>
  )}
</Combobox>
```

#### Backend API:
```php
GET /tvs/v1/exercises?search=squat
Response: [
  {
    id: 123,
    name: "Barbell Back Squat",
    category: "Legs",
    equipment: ["Barbell", "Rack"],
    difficulty: "Intermediate",
    default_metric: "reps"
  },
  ...
]
```

### Phase 2: Circuit Grouping (Advanced)

#### Data Structure:
```javascript
workoutCircuits: [
  {
    id: 1,
    name: "Circuit 1",
    sets: 4, // How many rounds of this circuit
    exercises: [
      { 
        exercise_id: 123, // or null if custom
        name: "Squats", 
        reps: 10, 
        weight: 60,
        rest_seconds: 30
      },
      { 
        exercise_id: 124,
        name: "Push-ups", 
        reps: 15, 
        weight: 0,
        rest_seconds: 30
      }
    ]
  },
  {
    id: 2,
    name: "Circuit 2",
    sets: 3,
    exercises: [...]
  }
]
```

#### UI Structure:
```
┌─ Circuit 1 (4 rounds) ─────────────────┐
│ 1. Squats - 10 reps @ 60kg             │
│ 2. Push-ups - 15 reps (bodyweight)     │
│ 3. Lunges - 12 reps @ 20kg             │
│                                         │
│ [+ Add Exercise] [Edit] [Remove]       │
└─────────────────────────────────────────┘

┌─ Circuit 2 (3 rounds) ─────────────────┐
│ 1. Bench Press - 8 reps @ 80kg         │
│ 2. Rows - 10 reps @ 70kg               │
│                                         │
│ [+ Add Exercise] [Edit] [Remove]       │
└─────────────────────────────────────────┘

[+ Add Circuit]
```

#### Calculations:
```javascript
// Per circuit:
circuit1TotalReps = circuit.sets × circuit.exercises.reduce((sum, ex) => sum + ex.reps, 0)
// Example: 4 rounds × (10 + 15 + 12) = 4 × 37 = 148 reps

// Total volume per circuit:
circuit1Volume = circuit.sets × circuit.exercises.reduce((sum, ex) => sum + (ex.reps * ex.weight), 0)
// Example: 4 × (10×60 + 15×0 + 12×20) = 4 × 840 = 3360kg

// Workout totals:
totalReps = sum of all circuit reps
totalVolume = sum of all circuit volumes
```

### Phase 3: Time-based & Advanced Metrics

#### Support for time-based exercises:
```javascript
{
  name: "Plank",
  metric_type: "time", // vs "reps"
  duration_seconds: 60,
  sets: 3,
  weight: 0
}
```

#### Display:
- "Plank - 3 × 60s" instead of "Plank - 3 × 10"
- Total time calculation instead of reps

## Implementation Priority

### Immediate (Now):
✅ Better form labels (DONE)
✅ Column headers for inputs (DONE)

### Short-term (Next sprint):
1. Create `tvs_exercise` CPT
2. Admin UI for exercise library
3. REST API for exercise search
4. Autocomplete dropdown in manual tracker
5. Allow mixing library + custom exercises

### Medium-term:
1. Circuit grouping UI
2. Drag-and-drop exercise reordering
3. Circuit templates (save/load common workouts)
4. Rest timer between sets/circuits

### Long-term:
1. Video demonstrations per exercise
2. Personal records tracking
3. Workout recommendations
4. Exercise analytics (frequency, volume trends)
5. Export to training plan

## Technical Considerations

### Database Schema:
```sql
-- Existing (current):
wp_postmeta:
  _tvs_manual_exercises: JSON array of {name, sets, reps, weight}

-- With circuits (future):
wp_postmeta:
  _tvs_manual_circuits: JSON array of {name, sets, exercises[]}
  
-- With exercise library:
wp_posts (tvs_exercise):
  post_title, post_content, post_status
wp_postmeta:
  _tvs_exercise_equipment, _tvs_exercise_muscles, etc.
```

### Backward Compatibility:
- Current structure still valid (simple exercise list)
- Add `circuit_id` field to exercises for grouping
- If no circuits, treat as single "Main" circuit
- Frontend detects structure and renders accordingly

### Performance:
- Exercise search: Use WordPress search with caching
- Limit to 50 results, require 2+ characters
- Debounce search input (300ms)
- Cache common searches in localStorage

## UX Flow Examples

### Scenario 1: Beginner (Simple)
1. Start Workout activity
2. Type "squat" → see library results
3. Select "Barbell Back Squat"
4. Enter sets/reps/weight
5. Add more exercises
6. Finish → see totals

### Scenario 2: Advanced (Circuits)
1. Start Workout activity
2. Click "Add Circuit"
3. Name it "Superset A"
4. Add 2-3 exercises
5. Set circuit rounds
6. Add another circuit
7. Finish → see per-circuit and total stats

### Scenario 3: Custom Exercise
1. Start typing custom name
2. No library match found
3. Click "+ Add custom exercise"
4. Exercise added to activity only (not library)
5. Option to "Save to library" later

## Questions for Discussion

1. **Circuit grouping**: Phase 2 eller launch med enklere versjon først?
2. **Exercise library**: Skal vi pre-populate med vanlige øvelser eller la admin bygge opp?
3. **Time-based exercises**: Viktig nok for v1 eller kan vente?
4. **Rest timers**: Mellom sets, mellom øvelser, eller mellom circuits?
5. **Superset notation**: Separate fra circuits eller samme konsept?

## Recommended Approach

**For neste sprint:**
1. Forbedre UI med labels (✅ Done)
2. Lag exercise CPT + basic admin UI
3. Lag enkel autocomplete dropdown
4. Test med 20-30 vanlige øvelser
5. Samle feedback før circuit grouping

**Fordeler med gradvis rollout:**
- ✅ Raskere time-to-value
- ✅ Test brukeradferd før kompleks UI
- ✅ Enklere å validere teknisk arkitektur
- ✅ Kan justere basert på reell bruk

Hva tenker du? Skal vi starte med exercise library først? 🏋️‍♂️
