<?php 


Route::group(['middleware' => ['auth.localizaion-lang'], 
    'namespace' => 'Megaads\LaravelLocalization\Controllers'
], function () {
    Route::get('/lang-editor', [
            'as' => 'frontend::multilanguage::editor', 
            'uses' => 'LanguageController@langEditor']);

    Route::post('/lang-editor', [
        'as' => 'frontend::multilanguage::editor', 
        'uses' => 'LanguageController@langEditor']);

    Route::delete('/lang-editor/delete-item', [
        'as' => 'frontend::mutilanguage::delete::item', 
        'uses' => 'LanguageController@deleteItem']);
});

Route::group([
    'middleware' => ['auth.localizaion-lang'],
    'prefix' => 'localization/api',
    'namespace' => 'Megaads\LaravelLocalization\Controllers'
], function () {
    Route::get('/list-language', [
        'as' => 'frontend::multilanguage::list', 
        'uses' => 'LanguageApiController@listLanguage']);

    Route::post('/save-language', [
        'as' => 'frontend::multilanguage::save', 
        'uses' => 'LanguageApiController@saveLanguage']);

    Route::delete('/delete-language-item', [
        'as' => 'frontend::multilanguage::delete::item', 
        'uses' => 'LanguageApiController@deleteItem']);
});
