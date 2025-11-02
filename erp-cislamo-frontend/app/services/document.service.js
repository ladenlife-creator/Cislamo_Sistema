angular.module('erpCislamoApp')
    .service('DocumentService', ['ApiService', function(ApiService) {
        this.getAll = function() {
            return ApiService.get('/documents');
        };
        
        this.getById = function(id) {
            return ApiService.get('/documents/' + id);
        };
        
        this.create = function(document) {
            return ApiService.post('/documents', document);
        };
        
        this.update = function(id, document) {
            return ApiService.put('/documents/' + id, document);
        };
        
        this.delete = function(id) {
            return ApiService.delete('/documents/' + id);
        };
    }]);

