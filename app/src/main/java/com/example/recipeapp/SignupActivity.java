package com.example.recipeapp;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;
import com.example.recipeapp.databinding.ActivitySignupBinding;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.auth.FirebaseUser;

public class SignupActivity extends AppCompatActivity {

    ActivitySignupBinding binding;
    FirebaseAuth firebaseAuth;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivitySignupBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        firebaseAuth = FirebaseAuth.getInstance(); // Initialize Firebase Auth

        binding.signupButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String email = binding.signupEmail.getText().toString();
                String password = binding.signupPassword.getText().toString();
                String confirmPassword = binding.signupConfirm.getText().toString();

                if (email.isEmpty() || password.isEmpty() || confirmPassword.isEmpty()) {
                    Toast.makeText(SignupActivity.this, "All fields are mandatory", Toast.LENGTH_SHORT).show();
                } else {
                    if (validateEmail(email)) {
                        if (validatePassword(password)) {
                            if (password.equals(confirmPassword)) {
                                // Create user with Firebase Authentication
                                firebaseAuth.createUserWithEmailAndPassword(email, password)
                                        .addOnCompleteListener(task -> {
                                            if (task.isSuccessful()) {
                                                FirebaseUser user = firebaseAuth.getCurrentUser();
                                                // Send verification email
                                                if (user != null) {
                                                    user.sendEmailVerification()
                                                            .addOnCompleteListener(verificationTask -> {
                                                                if (verificationTask.isSuccessful()) {
                                                                    Toast.makeText(SignupActivity.this, "Signup Successfully. Please check your email to verify your account.", Toast.LENGTH_SHORT).show();
                                                                    Intent intent = new Intent(getApplicationContext(), LoginActivity.class);
                                                                    startActivity(intent);
                                                                } else {
                                                                    Toast.makeText(SignupActivity.this, "Failed to send verification email: " + verificationTask.getException().getMessage(), Toast.LENGTH_SHORT).show();
                                                                }
                                                            });
                                                }
                                            } else {
                                                Toast.makeText(SignupActivity.this, "Signup Failed: " + task.getException().getMessage(), Toast.LENGTH_SHORT).show();
                                            }
                                        });
                            } else {
                                Toast.makeText(SignupActivity.this, "Passwords do not match", Toast.LENGTH_SHORT).show();
                            }
                        } else {
                            Toast.makeText(SignupActivity.this, "Password must be at least 8 characters long", Toast.LENGTH_SHORT).show();
                        }
                    } else {
                        Toast.makeText(SignupActivity.this, "Invalid Email Format", Toast.LENGTH_SHORT).show();
                    }
                }
            }
        });

        // Add click listener to navigate to LoginActivity
        binding.loginRedirectText.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(SignupActivity.this, LoginActivity.class);
                startActivity(intent);
            }
        });
    }

    private boolean validateEmail(String email) {
        String emailPattern = "^[\\w.%+-]+@[\\w.-]+\\.[a-zA-Z]{2,}$";
        return email.matches(emailPattern);
    }

    private boolean validatePassword(String password) {
        return password.length() >= 8;
    }
}
