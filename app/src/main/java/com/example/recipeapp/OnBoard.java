package com.example.recipeapp;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import androidx.appcompat.app.AppCompatActivity;

public class OnBoard extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_onboard);  // Make sure this XML file exists and is named correctly

        // Find the button by its ID (make sure the button ID is correct)
        Button buttonGetStarted = findViewById(R.id.btn_getstarted);

        // Set an OnClickListener for the button
        buttonGetStarted.setOnClickListener(v -> {
            // Navigate to SignupActivity when the button is clicked
            Intent intent = new Intent(OnBoard.this, SignupActivity.class);  // Ensure SignupActivity exists and is spelled correctly
            startActivity(intent);
            finish();  // Optional: Prevent returning to onboarding screen
        });
    }
}
