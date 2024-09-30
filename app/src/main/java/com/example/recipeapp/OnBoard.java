package com.example.recipeapp;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import androidx.appcompat.app.AppCompatActivity;

public class OnBoard extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_onboard);

        Button buttonGetStarted = findViewById(R.id.btn_getstarted);

        buttonGetStarted.setOnClickListener(v -> {
            Intent intent = new Intent(OnBoard.this, LoginActivity.class);
            startActivity(intent);
            finish();
        });
    }
}
