package com.example.recipeapp;

import android.content.ContentValues;
import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;

public class myDbAdapter {
    myDbHelper myhelper;
    public myDbAdapter(Context context) {
        myhelper = new myDbHelper(context);
    }

    public long insertData(String email, String password) {
        SQLiteDatabase dbb = myhelper.getWritableDatabase();
        ContentValues contentValues = new ContentValues();
        contentValues.put(myhelper.getName(), email);
        contentValues.put(myhelper.getMyPASSWORD(), password);
        long id = dbb.insert(myhelper.getTableName(), null, contentValues);
        return id;
    }

    public boolean checkEmail(String email) {
        SQLiteDatabase db = myhelper.getWritableDatabase();
        String[] columns = {myhelper.UID, myhelper.getName(), myhelper.getMyPASSWORD()};
        String[] whereArgs = {email};
        Cursor cursor = db.query(myhelper.getTableName(), columns, myhelper.getName() + " = ?", whereArgs, null, null, null);
        return cursor.getCount() > 0;
    }
    public boolean checkPassword(String email, String password) {
        SQLiteDatabase db = myhelper.getWritableDatabase();
        String[] columns = {myhelper.getMyPASSWORD()};
        String[] selectionArgs = {email};
        String selection = myhelper.getName() + " = ?";

        Cursor cursor = db.query(myhelper.getTableName(), columns, selection, selectionArgs, null, null, null);
        if (cursor.getCount() > 0) {
            cursor.moveToFirst();
            int columnIndex = cursor.getColumnIndex(myhelper.getMyPASSWORD());
            if (columnIndex != -1) {
                String storedPassword = cursor.getString(columnIndex);
                return storedPassword.equals(password);
            } else {
                return false; // or throw an exception, depending on your requirements
            }
        } else {
            return false;
        }
    }
}