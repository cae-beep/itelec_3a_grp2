package com.example.recipeapp;

import android.os.Bundle;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.ScaleAnimation;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class MainActivity extends AppCompatActivity {

    private int selectedTab = 1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        final LinearLayout homeLayout = findViewById(R.id.homeLayout);
        final LinearLayout likeLayout = findViewById(R.id.likeLayout);
        final LinearLayout addLayout = findViewById(R.id.addLayout);
        final LinearLayout userLayout = findViewById(R.id.userLayout);

        final ImageView homeImage = findViewById(R.id.homeImage);
        final ImageView likeImage = findViewById(R.id.likeImage);
        final ImageView addImage = findViewById(R.id.addImage);
        final ImageView userImage = findViewById(R.id.userImage);

        final TextView homeText = findViewById(R.id.homeText);
        final TextView likeText = findViewById(R.id.likeText);
        final TextView addText = findViewById(R.id.addText);
        final TextView userText = findViewById(R.id.userText);

        // Load home fragment by default
        getSupportFragmentManager().beginTransaction()
                .setReorderingAllowed(true)
                .replace(R.id.fragment_container_view_tag, HomeFragment.class, null)
                .commit();

        homeLayout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // Check if home is already selected
                if (selectedTab != 1) {
                    getSupportFragmentManager().beginTransaction()
                            .setReorderingAllowed(true)
                                    .replace(R.id.fragment_container_view_tag, HomeFragment.class, null)
                                    .commit();
                    switchToTab(homeLayout, homeImage, homeText, R.drawable.home, R.drawable.round_back_home_100);
                    selectedTab = 1;
                }
            }
        });

        likeLayout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (selectedTab != 2) {
                    getSupportFragmentManager().beginTransaction()
                            .setReorderingAllowed(true)
                            .replace(R.id.fragment_container_view_tag, LikesFragment.class, null)
                            .commit();

                    switchToTab(likeLayout, likeImage, likeText, R.drawable.notif, R.drawable.round_back_notif_100);
                    selectedTab = 2;
                }
            }
        });

        addLayout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (selectedTab != 3) {
                    getSupportFragmentManager().beginTransaction()
                            .setReorderingAllowed(true)
                            .replace(R.id.fragment_container_view_tag, addFragment.class, null)
                            .commit();

                    switchToTab(addLayout, addImage, addText, R.drawable.add, R.drawable.round_back_add_100);
                    selectedTab = 3;
                }
            }
        });

        userLayout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (selectedTab != 4) {
                    getSupportFragmentManager().beginTransaction()
                            .setReorderingAllowed(true)
                            .replace(R.id.fragment_container_view_tag, userFragment.class, null)
                            .commit();

                    switchToTab(userLayout, userImage, userText, R.drawable.user, R.drawable.round_back_user_100);
                    selectedTab = 4;
                }
            }
        });
    }

    private void switchToTab(LinearLayout selectedLayout, ImageView selectedImage, TextView selectedText, int imageResource, int backgroundResource) {
        // Reset all other tabs
        resetTabs();

        // Show selected tab's text and set background
        selectedText.setVisibility(View.VISIBLE);
        selectedImage.setImageResource(imageResource);
        selectedLayout.setBackgroundResource(backgroundResource);

        // Animate the selected tab
        ScaleAnimation scaleAnimation = new ScaleAnimation(
                0.0f, 1.0f, // fromX, toX
                1.0f, 1.0f, // fromY, toY
                Animation.RELATIVE_TO_SELF, 0.5f, // pivotXType, pivotXValue
                Animation.RELATIVE_TO_SELF, 0.5f  // pivotYType, pivotYValue
        );
        scaleAnimation.setDuration(200);
        scaleAnimation.setFillAfter(true);
        selectedLayout.startAnimation(scaleAnimation);
    }

    private void resetTabs() {
        // Hide text and reset images for all tabs
        findViewById(R.id.homeText).setVisibility(View.GONE);
        findViewById(R.id.likeText).setVisibility(View.GONE);
        findViewById(R.id.addText).setVisibility(View.GONE);
        findViewById(R.id.userText).setVisibility(View.GONE);

        ((ImageView) findViewById(R.id.homeImage)).setImageResource(R.drawable.home);
        ((ImageView) findViewById(R.id.addImage)).setImageResource(R.drawable.add);
        ((ImageView) findViewById(R.id.userImage)).setImageResource(R.drawable.user);

        // Reset background for all layouts
        findViewById(R.id.homeLayout).setBackgroundColor(getResources().getColor(android.R.color.transparent));
        findViewById(R.id.likeLayout).setBackgroundColor(getResources().getColor(android.R.color.transparent));
        findViewById(R.id.addLayout).setBackgroundColor(getResources().getColor(android.R.color.transparent));
        findViewById(R.id.userLayout).setBackgroundColor(getResources().getColor(android.R.color.transparent));
    }
}