use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
      * Run the migrations.
    */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable();
            $table->string('password')->nullable(); // Manual login ke liye required, social login ke liye nullable
            $table->string('pin_code', 4)->nullable();
            $table->rememberToken();
            $table->string('currency', 3)->default('USD');
            $table->string('profile_image_url')->nullable();
            $table->string('role')->default('user');
            $table->string('api_token', 80)->unique()->nullable();
            $table->string('provider')->nullable(); // Social login provider (e.g., Google, Facebook)
            $table->string('provider_id')->nullable()->unique(); // Provider se aaya unique user ID
            $table->string('login_method')->default('manual'); // 'manual' or 'social'
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
