package com.example.recipeapp;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

public class myDbHelper extends SQLiteOpenHelper {
    // Increment the database version to 2 to apply changes
    private static final int DATABASE_VERSION = 2;
    private static final String DATABASE_NAME = "users.db";

    // Users Table
    private static final String TABLE_NAME_USERS = "users";
    public static final String UID = "_id";
    public static final String NAME = "email";
    public static final String MY_PASSWORD = "password";

    // Recipes Table
    private static final String TABLE_NAME_RECIPES = "recipes";
    private static final String RECIPE_ID = "_id";
    private static final String DISH_NAME = "dish_name";
    private static final String CATEGORY = "category";
    private static final String PROCEDURE = "procedure";
    private static final String DESCRIPTION = "description";
    private static final String INGREDIENTS = "ingredients";

    public myDbHelper(Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        // Create users table
        String CREATE_USERS_TABLE = "CREATE TABLE " + TABLE_NAME_USERS + " (" +
                UID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                NAME + " TEXT, " +
                MY_PASSWORD + " TEXT)";
        db.execSQL(CREATE_USERS_TABLE);

        // Create recipes table
        String CREATE_RECIPES_TABLE = "CREATE TABLE " + TABLE_NAME_RECIPES + " (" +
                RECIPE_ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                DISH_NAME + " TEXT, " +
                CATEGORY + " TEXT, " +
                PROCEDURE + " TEXT, " +
                DESCRIPTION + " TEXT, " +
                INGREDIENTS + " TEXT)";
        db.execSQL(CREATE_RECIPES_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        if (oldVersion < 2) {
            // If upgrading from version 1 to 2, create the recipes table
            String CREATE_RECIPES_TABLE = "CREATE TABLE " + TABLE_NAME_RECIPES + " (" +
                    RECIPE_ID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                    DISH_NAME + " TEXT, " +
                    CATEGORY + " TEXT, " +
                    PROCEDURE + " TEXT, " +
                    DESCRIPTION + " TEXT, " +
                    INGREDIENTS + " TEXT)";
            db.execSQL(CREATE_RECIPES_TABLE);
        }
    }

    // Method to insert a recipe into the recipes table
    public void insertRecipe(String dishName, String category, String procedure, String description, String ingredients) {
        SQLiteDatabase db = this.getWritableDatabase();
        ContentValues values = new ContentValues();
        values.put(DISH_NAME, dishName);
        values.put(CATEGORY, category);
        values.put(PROCEDURE, procedure);
        values.put(DESCRIPTION, description);
        values.put(INGREDIENTS, ingredients);

        db.insert(TABLE_NAME_RECIPES, null, values);
        db.close();
    }

    // Other existing methods...
    public boolean checkEmailExistence(String email) {
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.query(TABLE_NAME_USERS, new String[]{NAME}, NAME + " = ?", new String[]{email}, null, null, null);
        boolean exists = cursor.getCount() > 0;
        cursor.close();
        return exists;
    }

    public boolean checkEmailPassword(String email, String password) {
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.query(TABLE_NAME_USERS, new String[]{MY_PASSWORD}, NAME + " = ?", new String[]{email}, null, null, null);
        boolean passwordValid = false;
        if (cursor.moveToFirst()) {
            String storedPassword = cursor.getString(0);
            passwordValid = storedPassword.equals(password);
        }
        cursor.close();
        return passwordValid;
    }
}
