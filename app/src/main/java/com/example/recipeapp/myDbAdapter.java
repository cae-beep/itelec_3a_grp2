package com.example.recipeapp;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;

public class myDbAdapter {
    private myDbHelper myhelper;

    public myDbAdapter(Context context) {
        myhelper = new myDbHelper(context);
    }

    // Insert User Data
    public long insertUserData(String email, String password) {
        SQLiteDatabase db = myhelper.getWritableDatabase();
        ContentValues contentValues = new ContentValues();
        contentValues.put("email", email); // Replaced myhelper.getName() with the actual column name
        contentValues.put("password", password); // Replaced myhelper.getMyPASSWORD() with the actual column name
        long id = db.insert("users", null, contentValues); // Replaced myhelper.getTableName() with the actual table name
        db.close(); // Close the database after inserting data
        return id;
    }

    // Insert Recipe Data
    public long insertRecipeData(String dishName, String category, String procedure, String description, String ingredients) {
        SQLiteDatabase db = myhelper.getWritableDatabase();
        ContentValues contentValues = new ContentValues();
        contentValues.put("dish_name", dishName);
        contentValues.put("category", category);
        contentValues.put("procedure", procedure);
        contentValues.put("description", description);
        contentValues.put("ingredients", ingredients);
        long id = db.insert("recipes", null, contentValues);
        db.close(); // Close the database after inserting data
        return id;
    }

    // Check if email exists
    public boolean checkEmail(String email) {
        SQLiteDatabase db = myhelper.getReadableDatabase();
        String[] columns = {"_id", "email", "password"}; // Replaced myhelper.getName() and myhelper.getMyPASSWORD()
        String[] whereArgs = {email};
        Cursor cursor = db.query("users", columns, "email = ?", whereArgs, null, null, null); // Corrected the query
        boolean exists = cursor.getCount() > 0;
        cursor.close(); // Close the cursor
        db.close(); // Close the database
        return exists;
    }

    // Check if password matches
    public boolean checkPassword(String email, String password) {
        SQLiteDatabase db = myhelper.getReadableDatabase();
        String[] columns = {"password"}; // Replaced myhelper.getMyPASSWORD() with the actual column name
        String[] selectionArgs = {email};
        String selection = "email = ?"; // Replaced myhelper.getName() with the actual column name

        Cursor cursor = db.query("users", columns, selection, selectionArgs, null, null, null);
        boolean isValidPassword = false;

        if (cursor.moveToFirst()) {
            int columnIndex = cursor.getColumnIndex("password"); // Replaced myhelper.getMyPASSWORD() with the actual column name
            if (columnIndex != -1) {
                String storedPassword = cursor.getString(columnIndex);
                isValidPassword = storedPassword.equals(password);
            }
        }
        cursor.close(); // Close the cursor
        db.close(); // Close the database
        return isValidPassword;
    }

    // Retrieve All Recipes
    public Cursor getAllRecipes() {
        SQLiteDatabase db = myhelper.getReadableDatabase();
        String[] columns = {"_id", "dish_name", "category", "procedure", "description", "ingredients"};
        Cursor cursor = db.query("recipes", columns, null, null, null, null, null);
        return cursor; // Remember to close the cursor where you use it
    }

    // Retrieve a specific recipe by ID
    public Cursor getRecipeById(int id) {
        SQLiteDatabase db = myhelper.getReadableDatabase();
        String[] columns = {"_id", "dish_name", "category", "procedure", "description", "ingredients"};
        String selection = "_id = ?";
        String[] selectionArgs = {String.valueOf(id)};
        Cursor cursor = db.query("recipes", columns, selection, selectionArgs, null, null, null);
        return cursor; // Remember to close the cursor where you use it
    }
}
