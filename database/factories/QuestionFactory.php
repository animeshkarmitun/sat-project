use App\Models\Question;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        return [
            'section_id' => Section::factory(), // Automatically create a Section if not provided
            'question_text' => $this->faker->sentence(),
            'question_type' => $this->faker->randomElement(['multiple-choice', 'true/false', 'short-answer']),
            'difficulty_level' => $this->faker->numberBetween(1, 5),
            'is_active' => $this->faker->boolean(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
