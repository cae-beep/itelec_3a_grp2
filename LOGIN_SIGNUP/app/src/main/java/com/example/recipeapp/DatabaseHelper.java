package com.example.recipeapp;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

import androidx.annotation.Nullable;

public class DatabaseHelper extends SQLiteOpenHelper {

    public static final String databaseName = "Signup.db";

    public DatabaseHelper(@Nullable Context context) {
        super(context, "Signup.db", null, 1);
    }

    @Override
    public void onCreate(SQLiteDatabase MyDatabase) {
        // Create table 'allusers' with 'email' as primary key and 'password' as TEXT
        MyDatabase.execSQL("CREATE TABLE allusers (email TEXT PRIMARY KEY, password TEXT)");
    }

    @Override
    public void onUpgrade(SQLiteDatabase MyDatabase, int i, int i1) {
        // Drop the existing 'allusers' table and recreate it on upgrade
        MyDatabase.execSQL("DROP TABLE IF EXISTS allusers");
        onCreate(MyDatabase);
    }

    // Method to insert user data (email and password) into 'allusers' table
    public Boolean insertData(String email, String password){
        SQLiteDatabase myDatabase = this.getWritableDatabase();
        ContentValues contentValues = new ContentValues();
        contentValues.put("email", email);
        contentValues.put("password", password);

        // Insert data into the correct table 'allusers'
        long result = myDatabase.insert("allusers", null, contentValues);

        return result != -1; // Return true if insert succeeded
    }

    // Method to check if email already exists in 'allusers' table
    public Boolean checkEmail(String email){
        SQLiteDatabase MyDatabase = this.getWritableDatabase();
        Cursor cursor = MyDatabase.rawQuery("SELECT * FROM allusers WHERE email = ?", new String[]{email});

        return cursor.getCount() > 0; // Return true if email exists
    }

    // Method to check if email and password match an existing user in 'allusers' table
    public Boolean checkEmailPassword(String email, String password){
        SQLiteDatabase MyDatabase = this.getWritableDatabase();
        Cursor cursor = MyDatabase.rawQuery("SELECT * FROM allusers WHERE email = ? AND password = ?", new String[]{email, password});

        return cursor.getCount() > 0; // Return true if a match is found
    }
}
