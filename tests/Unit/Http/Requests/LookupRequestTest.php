<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\LookupRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LookupRequestTest extends TestCase
{
    private LookupRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new LookupRequest;
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_expected_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('username', $rules);
        $this->assertArrayHasKey('id', $rules);

        $this->assertContains('required', $rules['type']);
        $this->assertContains('in:minecraft,steam,xbl', $rules['type']);

        $this->assertEquals('required_without:id', $rules['username']);
        $this->assertEquals('required_without:username', $rules['id']);
    }

    public function test_validation_passes_with_valid_minecraft_username(): void
    {
        $data = [
            'type' => 'minecraft',
            'username' => 'TestPlayer',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_valid_steam_id(): void
    {
        $data = [
            'type' => 'steam',
            'id' => '76561198000000000',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_valid_xbl_username(): void
    {
        $data = [
            'type' => 'xbl',
            'username' => 'TestGamer',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_both_username_and_id(): void
    {
        $data = [
            'type' => 'minecraft',
            'username' => 'TestPlayer',
            'id' => 'test-uuid-123',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_without_type(): void
    {
        $data = [
            'username' => 'TestPlayer',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_platform_type(): void
    {
        $data = [
            'type' => 'invalid-platform',
            'username' => 'TestPlayer',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_validation_fails_without_username_or_id(): void
    {
        $data = [
            'type' => 'minecraft',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('username', $validator->errors()->toArray());
        $this->assertArrayHasKey('id', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_empty_values_when_other_is_present(): void
    {
        $data = [
            'type' => 'minecraft',
            'username' => 'TestPlayer',
            'id' => '',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_with_all_supported_platform_types(): void
    {
        $supportedPlatforms = ['minecraft', 'steam', 'xbl'];

        foreach ($supportedPlatforms as $platform) {
            $data = [
                'type' => $platform,
                'username' => 'TestUser',
            ];

            $validator = Validator::make($data, $this->request->rules());

            $this->assertTrue($validator->passes(), "Validation should pass for platform: $platform");
        }
    }

    public function test_validation_fails_with_case_sensitive_platform_names(): void
    {
        $invalidCases = ['MINECRAFT', 'Steam', 'XBL', 'Minecraft', 'MineCraft'];

        foreach ($invalidCases as $platform) {
            $data = [
                'type' => $platform,
                'username' => 'TestUser',
            ];

            $validator = Validator::make($data, $this->request->rules());

            $this->assertTrue($validator->fails(), "Validation should fail for platform: $platform");
            $this->assertArrayHasKey('type', $validator->errors()->toArray());
        }
    }
}
