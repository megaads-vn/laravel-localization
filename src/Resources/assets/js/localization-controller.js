localization.controller('LocalizationController', LocalizationController);

localization.config(['$httpProvider', function($httpProvider) {
    const encodedCredentials = btoa(`${USERNAME}:${PASSWORD}`);
    $httpProvider.defaults.headers.common['Authorization'] = `Basic ${encodedCredentials}`;
}])
.config(function($locationProvider){
    $locationProvider.html5Mode({
        enabled    :true,
        requireBase:false
    });
})
.directive('focusOnShow', function($timeout) {
    return {
        link: function(scope, element, attrs) {
            scope.$watch(attrs.focusOnShow, function(newValue) {
                if (newValue) {
                    $timeout(function() {
                        element[0].focus();
                    });
                }
            });
        }
    };
});

function LocalizationController($scope, $http, $location) {

    $scope.languages = [];
    $scope.locales = [
        {code: 'en', name: 'English'},
        {code: 'jp', name: 'Japanese'},
    ];
    $scope.filter = {};
    $scope.item = {};
    $scope.isSaving = false;
    $scope.message = "";
    $scope.errorMessage = "";

    this.initialize = function() {
        $scope.localeDefault();
        console.log('LocalizationController initialized');
        $scope.getLanguages();
    }

    $scope.getLanguages = function() {
        let urlParams = {
            locale: $scope.filter.locale.code
        }
        $http.get('/localization/api/list-language', {params: urlParams})
            .then(function(res) {
                const response = res.data;
                if (response.status === 'successful') {
                    const langKeys = Object.keys(response.data);
                    let displayData = [];
                    for (const keys of langKeys) {
                        displayData.push({
                            key: keys,
                            value: response.data[keys]
                        });
                    }
                    $scope.languages = displayData;
                }
        });
    }

    $scope.changeLocale = function() {
        const selectedLocale = $scope.filter.locale.code;
        $location.search('locale', selectedLocale);
        $scope.getLanguages();
    }

    $scope.localeDefault = function() {
        const localeFromUrl = $location.search().locale;
        if (localeFromUrl) {
            const matchingLocale = $scope.locales.find(locale => locale.code === localeFromUrl);
            if (matchingLocale) {
                $scope.filter.locale = matchingLocale;
            } else {
                $scope.filter.locale = $scope.locales[0];
            }
        } else {
            $scope.filter.locale = $scope.locales[0];
        }
    }

    $scope.showInput = function(item) {
        $scope.item = item;
        $scope.item.isShowInput = true;
        $scope.languages = $scope.languages.map(lang => {
            if (lang.key === item.key) {
                lang.is_show_input = true;
            }
            return lang;
        });
    }

    $scope.hideInput = function(item) {
        let forceHide = true;
        if ($scope.item.value != "") {
            forceHide = confirm("Do you want to discard the changes?");
        }
        if (forceHide) {
            $scope.languages = $scope.languages.map(lang => {
                if (lang.key === item.key) {
                    lang.is_show_input = false;
                }
                return lang;
            });
            $scope.item = {};
        }
    }

    $scope.updateItem = function() {
        const urlParams = {
            key: $scope.item.key,
            value: $scope.item.value,
            locale: $scope.filter.locale.code
        }
        $http.post('/localization/api/save-language', urlParams)
            .then(function(res) {
                const response = res.data;
                if (response.status === 'successful') {
                    $scope.languages = $scope.languages.map(lang => {
                        if (lang.key === $scope.item.key) {
                            lang.value = $scope.item.value;
                            lang.is_show_input = false;
                        }
                        return lang;
                    });
                    $scope.item = {};
                }
        });
    }

    $scope.deleteItem = function(item) {
        const isDelete = confirm(`Do you want to delete this item (${item.key})?`);
        if (isDelete) {
            const urlParams = {
                key: item.key,
                locale: $scope.filter.locale.code
            }
            $http.delete('/localization/api/delete-language-item', {params: urlParams})
                .then(function(res) {
                    const response = res.data;
                    if (response.status === 'successful') {
                        $scope.languages = $scope.languages.filter(lang => lang.key !== item.key);
                    }
            });
        }
    }

    $scope.openModal = function() {
        $scope.item = {};
        $scope.errorMessage = "";
        $scope.message = "";
        $scope.isSaving = false;
        $('#add-key-modal').modal();
    }

    $scope.closeModal = function () {
        if ((typeof $scope.item.key !== "undefined" && $scope.item.key != "")
            || (typeof $scope.item.value !== "undefined" && $scope.item.value != "")) {
            const forceClose = confirm("Do you want to discard the changes?");
            if (forceClose) {
                $scope.item = {};
                $('#add-key-modal').modal('hide');
            }
        } else {
            $scope.item = {};
            $('#add-key-modal').modal('hide');
        }
    }

    $scope.saveItem = function() {
        let isValid = true;
        if ($scope.item.key == "" || typeof $scope.item.key === "undefined") {
            $scope.errorMessage = "Key is required";
            isValid = false;
        }
        if (isValid) {
            $scope.errorMessage = "";
            const saveParams = {
                key: $scope.item.key, 
                value: $scope.item.value ? $scope.item.value : "",
                locale: $scope.filter.locale.code
            };
            $http.post('/localization/api/save-language', saveParams)
                .then(function(res) {
                    const response = res.data;
                    if (response.status === 'successful') {
                        $scope.languages.push($scope.item);
                        $scope.message = "Add key successful";
                    } else {
                        $scope.errorMessage = response.message;
                    }
            });
        }
    }

    this.initialize();
}