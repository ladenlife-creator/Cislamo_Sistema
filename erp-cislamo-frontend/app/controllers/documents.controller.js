angular.module('erpCislamoApp')
    .controller('DocumentsController', ['$scope', '$location', 'AuthService', 'DocumentService', function($scope, $location, AuthService, DocumentService) {
        $scope.documents = [];
        $scope.loading = true;
        $scope.showForm = false;
        $scope.currentUser = AuthService.getUser();
        
        $scope.newDocument = {
            title: '',
            description: '',
            category: ''
        };
        
        $scope.loadDocuments = function() {
            DocumentService.getAll()
                .then(function(response) {
                    $scope.documents = response.data;
                })
                .catch(function(error) {
                    console.error('Erro ao carregar documentos:', error);
                })
                .finally(function() {
                    $scope.loading = false;
                });
        };
        
        $scope.toggleForm = function() {
            $scope.showForm = !$scope.showForm;
            if (!$scope.showForm) {
                $scope.resetForm();
            }
        };
        
        $scope.deleteDocument = function(docId) {
            if (confirm('Tem certeza que deseja remover este documento?')) {
                DocumentService.delete(docId)
                    .then(function() {
                        $scope.documents = $scope.documents.filter(function(d) { return d.id !== docId; });
                        alert('Documento removido com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao remover documento.');
                    });
            }
        };
        
        $scope.resetForm = function() {
            $scope.newDocument = {
                title: '',
                description: '',
                category: ''
            };
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.goTo = function(path) {
            $location.path(path);
        };
        
        $scope.loadDocuments();
    }]);

