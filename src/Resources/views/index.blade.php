<!DOCTYPE html>
<html lang="en" ng-app="localization">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Multiple Language File Editor | {{ ucfirst(env('APP_NAME', '')) }}</title>
    <link rel="shortcut icon" sizes="16x16" href="/vendor/localization/assets/images/icon.png" type="image/x-icon" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="/vendor/localization/assets/css/package.css?v=2022-12-28" rel="stylesheet" type="text/css" />

    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-css/1.4.6/select2-bootstrap.min.css" rel="stylesheet" type="text/css" /> --}}
    <script src="/vendor/localization/assets/js/jquery.min.1.12.4.js" type="text/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script>
        const USERNAME = "{{ Config::get('localization.auth.basic.username') }}";
        const PASSWORD = "{{ Config::get('localization.auth.basic.password') }}";
        const localization = angular.module('localization', []);
    </script>
    <style>
        .language-title {
            display: flex;
            justify-content: center;
            align-items: center;
            justify-items: flex-start;
        }
        .language-title h5 {
            font-size: 24px;
            padding-left: 15px;
        }
        .success-color {
            color: #3c763d;
        }
        .lang-text {
            cursor: pointer;
        }
        .edit-input-wrapper {
            display: flex;
            justify-content: space-around;
            align-items: center;
            justify-items: center;
            padding: 0 3px;
        }
        .edit-input-wrapper input[type="text"] {
            width: 90%;
        }
        .msg-error {
            color: red;
        }
        .msg-success {
            color: green;
        }
        .footer-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-top: 1px solid #ccc; 
        }
        .result-message {
            flex: 1;
            text-align: left;
        }
        .button-group {
            display: flex;
            gap: 10px; /* Khoảng cách giữa các nút */
        }

        .button-group .btn {
            margin-left: auto; /* Đẩy các nút về phía bên phải */
        }
    </style>
</head>

<body ng-controller="LocalizationController">
    <div class="container">
        <div class="row" style="margin:15px 0px">
            <div class="col-sm-12 col-md-12 col-lg-12 text-center language-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-translate success-color" viewBox="0 0 16 16">
                    <path d="M4.545 6.714 4.11 8H3l1.862-5h1.284L8 8H6.833l-.435-1.286H4.545zm1.634-.736L5.5 3.956h-.049l-.679 2.022H6.18z"/>
                    <path d="M0 2a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v3h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3H2a2 2 0 0 1-2-2V2zm2-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2zm7.138 9.995c.193.301.402.583.63.846-.748.575-1.673 1.001-2.768 1.292.178.217.451.635.555.867 1.125-.359 2.08-.844 2.886-1.494.777.665 1.739 1.165 2.93 1.472.133-.254.414-.673.629-.89-1.125-.253-2.057-.694-2.82-1.284.681-.747 1.222-1.651 1.621-2.757H14V8h-3v1.047h.765c-.318.844-.74 1.546-1.272 2.13a6.066 6.066 0 0 1-.415-.492 1.988 1.988 0 0 1-.94.31z"/>
                </svg>
                <h5>Multiple Languge File Editor</h5>
            </div>
        </div>
        <div class="row">
            <div class="add-key-btn">
                <button class="btn btn-success" title="Add new key" ng-click="openModal();"> <i class="fa fa-plus"></i> </button>
                <!-- <a href="/localization/export?locale=<?= isset($locale) ? $locale : 'en' ?>" class="btn btn-success" title="Export keys"> <i class="fa fa-download"></i> </a>
                <form action="/localization/import" method="post" enctype="multipart/form-data">
                    <label class="btn btn-success" title="Import keys" for="fileImport"> <i class="fa fa-upload"></i> </label>
                    <input type="hidden" name="locale" value="<?= isset($locale) ? $locale : 'en' ?>">
                    <input type="file" name="content" id="fileImport" style="display: none" onchange="this.form.submit()">
                </form> -->
            </div>
            <div class="col-sm-12 col-md-12 col-lg-12">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <span class="glyphicon glyphicon-hand-right"></span>
                            &nbsp;
                            <select name="select-locale" ng-model="filter.locale" 
                            ng-options="lc.name for lc in locales" ng-change="changeLocale();"></select>
                        </h3>
                        <span class="pull-right clickable"><i class="glyphicon glyphicon-chevron-up"></i></span>
                    </div>
                    <div class="panel-body">
                        <table class="table table-hover table-bordered table-store-for-search">
                            <thead>
                                <tr>
                                    <th class="col-md-1 text-center vertical-align">Index</th>
                                    <th class="col-md-5 text-center vertical-align">Key</th>
                                    <th class="col-md-6 text-center vertical-align">Value</th>
                                    <th class="col-md-6 text-center vertical-align">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="lang in languages" ng-show="languages.length > 0">
                                    <td class="text-center vertical-align">@{{ $index + 1 }}</td>
                                    <td class="vertical-align">@{{ lang.key }}</td>
                                    <td class="text-center vertical-align lang-value" data-value="@{{ lang.key }}">
                                        <span class="lang-text" ng-click="showInput(lang);" ng-show="!lang.is_show_input">@{{ lang.value }}</span>
                                        <div class="edit-input-wrapper" ng-show="lang.is_show_input">
                                            <input type="text" class="form-control" ng-model="item.value" name="editor" focus-on-show="item.key == lang.key"/>
                                            <button class="btn btn-success" ng-click="updateItem();"><i class="fa fa-save"></i></button>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger" ng-click="deleteItem(lang);"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                
                                <tr class="config-coupon-nodata" ng-show="languages.length <= 0">
                                    <td colspan="3">No data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('localization::modal')
</body>
<script type="text/javascript">
    var locale = "<?= isset($locale) ? $locale : 'en' ?>";
    var deleteRoute = "<?= route('frontend::mutilanguage::delete::item') ?>";
</script>
<script src="/vendor/localization/assets/js/localization-controller.js?v={{ time() }}"></script>
</html>