angular.module('erpCislamoApp')
    .service('ApiService', ['$http', 'AuthService', function($http, AuthService) {
        var API_URL = 'http://localhost:8000/api';
        
        this.get = function(endpoint) {
            return $http.get(API_URL + endpoint, {
                headers: { 'Authorization': 'Bearer ' + AuthService.getToken() }
            });
        };
        
        this.post = function(endpoint, data) {
            return $http.post(API_URL + endpoint, data, {
                headers: { 'Authorization': 'Bearer ' + AuthService.getToken() }
            });
        };
        
        this.put = function(endpoint, data) {
            return $http.put(API_URL + endpoint, data, {
                headers: { 'Authorization': 'Bearer ' + AuthService.getToken() }
            });
        };
        
        this.delete = function(endpoint) {
            return $http.delete(API_URL + endpoint, {
                headers: { 'Authorization': 'Bearer ' + AuthService.getToken() }
            });
        };
    }]);

