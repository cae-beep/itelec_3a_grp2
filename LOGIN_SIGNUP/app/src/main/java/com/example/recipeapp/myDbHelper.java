package com.example.recipeapp;

import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;

public class myDbHelper extends SQLiteOpenHelper {
    private static final int DATABASE_VERSION = 1;
    private static final String DATABASE_NAME = "users.db";
    // Table Name
    private static final String TABLE_NAME = "users";
    // Column names
    public static final String UID = "_id";
    public static final String NAME = "email";
    public static final String MY_PASSWORD = "password";

    public myDbHelper(Context context) {
        super(context, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        String CREATE_TABLE = "CREATE TABLE " + TABLE_NAME + " (" +
                UID + " INTEGER PRIMARY KEY AUTOINCREMENT, " +
                NAME + " TEXT, " +
                MY_PASSWORD + " TEXT)";
        db.execSQL(CREATE_TABLE);
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS " + TABLE_NAME);
        onCreate(db);
    }

    public String getTableName() {
        return TABLE_NAME;
    }

    public String getName() {
        return NAME;
    }

    public String getMyPASSWORD() {
        return MY_PASSWORD;
    }

    public boolean checkEmailExistence(String email) {
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.query("users", new String[]{"email"}, "email = ?", new String[]{email}, null, null, null);
        boolean exists = cursor.getCount() > 0;
        cursor.close();
        return exists;
    }

    public boolean checkEmailPassword(String email, String password) {
        SQLiteDatabase db = this.getReadableDatabase();
        Cursor cursor = db.query("users", new String[]{"password"}, "email = ?", new String[]{email}, null, null, null);
        boolean passwordValid = false;
        if (cursor.moveToFirst()) {
            String storedPassword = cursor.getString(0);
            passwordValid = storedPassword.equals(password);
        }
        cursor.close();
        return passwordValid;
    }
}