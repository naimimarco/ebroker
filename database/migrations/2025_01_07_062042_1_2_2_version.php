<?php

use App\Models\Setting;
use App\Models\Property;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add Profile column in users
        if (!Schema::hasColumn('users', 'profile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text("profile")->nullable(true)->after('name');
            });
        }

        // Add web_image column in slider with image nullable
        if (!Schema::hasColumn('sliders', 'web_image')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->text('web_image')->after('image')->nullable(true);
                $table->text('image')->comment('app_image')->nullable(true)->change();
            });
        }

        // Add email with nullable in number_otps and make number nullable
        if (!Schema::hasColumn('number_otps', 'email')) {
            Schema::table('number_otps', function (Blueprint $table) {
                $table->string("email")->unique(true)->nullable(true)->after("id");
                $table->string("number")->nullable(true)->change();
            });
        }

        // Add Password and is_email_verified column, add 3 login type in comment
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'password')) {
                $table->string("password")->after('email')->nullable(true);
            }
            if (!Schema::hasColumn('customers', 'is_email_verified')) {
                $table->boolean("is_email_verified")->after('logintype')->default(false);
            }
            $table->string("logintype")->comment("0 - Gmail, 1 - Number, 2 - Apple, 3 - Email")->change();
        });


        // Add user_side_status and request_status column in property
        Schema::table('propertys', function (Blueprint $table) {
            if (!Schema::hasColumn('propertys', 'request_status')) {
                $table->enum('request_status',['approved','rejected','pending'])->default('pending')->after('status');
            }
        });

        // Reject Reasons Table
        Schema::create('reject_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable(true)->references('id')->on('propertys')->onDelete('cascade');
            $table->foreignId('project_id')->nullable(true)->references('id')->on('projects')->onDelete('cascade');
            $table->longText("reason");
            $table->timestamps();
            $table->softDeletes();
        });

        //Drop existing password reset table and add new table with same name but different columns for positions
        /** As id was missing and laravel doesn't support to place column at first in current table exists */
        Schema::dropIfExists('password_resets');
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('token');
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        // Make Mobile number nullable
        if (Schema::hasColumn('customers', 'mobile')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('mobile')->nullable(true)->change();
            });
        }


        /** Update Setting Data */
        $verifyMailData = "<p>Hello,</p> <p>Thank you for signing up with <strong>{app_name}</strong>. To complete your registration and verify your email address, please use the following One-Time Password (OTP): <strong>{otp}</strong>. This OTP is valid for the next 10 minutes.</p> <p>If you did not initiate this request, please ignore this email.</p> <p>Best regards,</p> <p>The <strong>{app_name} </strong>Team</p>";
        $propertyStatusMailTemplate = "<p>Hello <strong>{user_name}</strong>,</p> <p>We wanted to inform you that the status of your property, <strong>{property_name}</strong>, has been <strong>{status}</strong>.</p> <p><strong>{reject_reason}</strong></p> <p>If you have any questions or need further assistance, please feel free to reach out to our support team.</p> <p>Best regards,</p> <p>The <strong>{app_name}</strong>&nbsp;Team</p> <div>&nbsp;</div>";
        $projectStatusMailTemplate = "<p>Hello <strong>{user_name}</strong>,</p> <p>We wanted to inform you that the status of your project, <strong>{project_name}</strong>, in <strong>{app_name}</strong> has been <strong>{status}</strong>.</p> <p>If you have any questions or need further assistance, please feel free to reach out to our support team.</p> <p>Best regards,</p> <p>The <strong>{app_name}</strong>&nbsp;Team</p>";
        $propertyFeatureMailTemplate = "<p>Dear<strong> <strong>{user_name}</strong>,</strong></p> <p>We are excited to inform you about the latest update regarding your property on <strong>{app_name}</strong>.</p> <p>Property Name: <strong>{property_name}</strong>,&nbsp;Advertisement Status: <strong>{advertisement_status}</strong></p> <p>If you have any questions or need further assistance, please feel free to reach out to our support team.</p> <p>Thank you for using <strong>{app_name}</strong>!<br /><br /></p> <p>Best regards,</p> <p><strong>{app_name}</strong>,</p> <p><strong>Support Team</strong></p>";
        $userStatusMailTemplate = "<p>Hello <strong>{user_name}</strong>,</p> <p>We wanted to inform you that your account status on <strong>{app_name}</strong> has been updated to <strong>{status}</strong>.</p> <p>If you have any questions or need further assistance, please feel free to reach out to our support team.</p> <p>Best regards,</p> <p>The <strong>{app_name}</strong>&nbsp;Team</p>";
        $passwordResetMailTemplate = "<p>Hello,</p> <p>We received a request to reset your password for your <strong>{app_name}</strong>&nbsp;account. To reset your password, please click on the following link:</p> <p><a title='{link}' href='{link}'><strong>{link}</strong></a></p> <p>If you did not request a password reset, please ignore this email or contact support for assistance.</p> <p>Best regards,</p> <p>The <strong>{app_name}</strong>&nbsp;Team</p>";
        $welcomeMailTemplate = "<p>Hello <strong>{user_name}</strong>,</p> <p>Welcome to <strong>{app_name}</strong>! We're thrilled to have you on board. Thank you for registering with us.</p> <p>Get ready to explore and enjoy all the features we have to offer. If you have any questions or need assistance, feel free to reach out to our support team.</p> <p>Best regards,</p> <p>The <strong>{app_name}</strong>&nbsp;Team</p>";
        $agentVerificationStatusTemplate = "<p><strong>Dear <strong>{user_name}</strong>,</strong></p> <p>We hope this email finds you well.</p> <p>We are writing to inform you about the current status of your agent verification process with <strong>{app_name}</strong>.</p> <p><strong>Status:</strong> <strong>{status}</strong></p> <p>If you have any questions or need further assistance, please don't hesitate to reach out to our support team.</p> <p>Thank you for your patience and cooperation.</p> <p>Best regards,</p> <p><strong>{app_name}</strong>,<br /><strong>Support Team</strong></p>";

        $settingsData = array(
            'email_configuration_verification' => 0,
            'verify_mail_template' => $verifyMailData,
            'property_status_mail_template' => $propertyStatusMailTemplate,
            'project_status_mail_template' => $projectStatusMailTemplate,
            'property_ads_mail_template' => $propertyFeatureMailTemplate,
            'user_status_mail_template' => $userStatusMailTemplate,
            'password_reset_mail_template' => $passwordResetMailTemplate,
            'welcome_mail_template' => $welcomeMailTemplate,
            'agent_verification_status_mail_template' => $agentVerificationStatusTemplate,
        );
        foreach ($settingsData as $key => $settingData) {
            // Adding default data for verification required for user settings
            Setting::updateOrCreate(['type' => $key],['data' => $settingData]);
        }

        // Update Admin properties to approved
        Property::where('added_by',0)->update(['request_status' => "approved"]);
        /******************************************************************************************* */

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop Profile
        if (Schema::hasColumn('users', 'profile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('profile');
            });
        }

        // Drop Web Image and make image not nullable
        if (Schema::hasColumn('sliders', 'web_image')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->dropColumn('web_image');
                $table->text('image')->comment('')->nullable(false)->change();
            });
        }

        // Drop Email and make number not nullable
        if (Schema::hasColumn('number_otps', 'email')) {
            Schema::table('number_otps', function (Blueprint $table) {
                $table->dropColumn('email');
                $table->string("number")->nullable(false)->change();
            });
        }

        // Drop Is email verified
        if (Schema::hasColumn('customers', 'is_email_verified')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('is_email_verified');
            });
        }

        // Drop password
        if (Schema::hasColumn('customers', 'password')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('password');
            });
        }

        // Drop request status
        if (Schema::hasColumn('propertys', 'request_status')) {
            Schema::table('propertys', function (Blueprint $table) {
                $table->dropColumn('request_status');
            });
        }

        // Drop Reject Reasons Table
        Schema::dropIfExists('reject_reasons');

        // Revert back to original password reset table
        Schema::dropIfExists('password_resets');
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        if (Schema::hasColumn('customers', 'mobile')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('mobile')->nullable(false)->change();
            });
        }
    }
};
