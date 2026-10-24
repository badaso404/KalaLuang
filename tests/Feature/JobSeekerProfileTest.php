<?php

namespace Tests\Feature;

use App\Enums\EducationLevel;
use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobSeekerProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function payload(array $overrides = []): array
    {
        return [
            'phone' => '081200000001',
            'city' => 'Jakarta',
            'education_level' => 'S1',
            'institution' => 'Universitas Contoh',
            'major' => 'Manajemen',
            'graduation_year' => 2025,
            'bio' => 'Lulusan baru yang siap belajar.',
            ...$overrides,
        ];
    }

    public function test_a_job_seeker_completes_their_profile_with_a_cv_and_skills(): void
    {
        Storage::fake('local');

        $profile = JobSeekerProfile::factory()->incomplete()->create();
        $skills = Skill::factory()->count(3)->create();

        $this->actingAs($profile->user)
            ->put(route('job-seeker.profile.update'), $this->payload([
                'skills' => $skills->pluck('id')->all(),
                'cv' => UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf'),
            ]))
            ->assertRedirect();

        $profile->refresh();

        $this->assertSame(EducationLevel::S1, $profile->education_level);
        $this->assertSame('Universitas Contoh', $profile->institution);
        $this->assertTrue($profile->isComplete());
        $this->assertCount(3, $profile->skills);
        Storage::disk('local')->assertExists($profile->cv_path);
    }

    public function test_education_fields_are_required(): void
    {
        $profile = JobSeekerProfile::factory()->incomplete()->create();

        $this->actingAs($profile->user)
            ->put(route('job-seeker.profile.update'), $this->payload([
                'education_level' => null,
                'institution' => null,
                'major' => null,
                'graduation_year' => null,
            ]))
            ->assertSessionHasErrors(['education_level', 'institution', 'major', 'graduation_year']);
    }

    public function test_the_cv_must_be_a_pdf_under_two_megabytes(): void
    {
        Storage::fake('local');

        $profile = JobSeekerProfile::factory()->incomplete()->create();

        $this->actingAs($profile->user)
            ->put(route('job-seeker.profile.update'), $this->payload([
                'cv' => UploadedFile::fake()->image('cv.jpg'),
            ]))
            ->assertSessionHasErrors('cv');

        $this->actingAs($profile->user)
            ->put(route('job-seeker.profile.update'), $this->payload([
                'cv' => UploadedFile::fake()->create('cv.pdf', 3000, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('cv');
    }

    public function test_replacing_the_cv_deletes_the_previous_file(): void
    {
        Storage::fake('local');

        $profile = JobSeekerProfile::factory()->incomplete()->create();

        $this->actingAs($profile->user)->put(route('job-seeker.profile.update'), $this->payload([
            'cv' => UploadedFile::fake()->create('lama.pdf', 100, 'application/pdf'),
        ]));

        $firstPath = $profile->refresh()->cv_path;

        $this->actingAs($profile->user)->put(route('job-seeker.profile.update'), $this->payload([
            'cv' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf'),
        ]));

        $secondPath = $profile->refresh()->cv_path;

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($secondPath);
    }

    public function test_the_portfolio_url_must_be_a_url(): void
    {
        $profile = JobSeekerProfile::factory()->incomplete()->create();

        $this->actingAs($profile->user)
            ->put(route('job-seeker.profile.update'), $this->payload(['portfolio_url' => 'bukan-url']))
            ->assertSessionHasErrors('portfolio_url');
    }

    public function test_the_profile_page_creates_a_profile_row_if_missing(): void
    {
        $user = User::factory()->jobSeeker()->create();

        $this->assertNull($user->jobSeekerProfile);

        $this->actingAs($user)->get(route('job-seeker.profile.edit'))->assertOk();

        $this->assertNotNull($user->refresh()->jobSeekerProfile);
    }
}
