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
            $scope.loading = true;
            $scope.documents = [];
            
            DocumentService.getAll()
                .then(function(response) {
                    console.log('Resposta completa documentos:', response);
                    console.log('response.data tipo:', typeof response.data);
                    console.log('response.data é array?', Array.isArray(response.data));
                    
                    // O AngularJS $http sempre retorna response.data
                    if (response && response.data) {
                        if (Array.isArray(response.data)) {
                            $scope.documents = response.data;
                            console.log('Documentos atribuídos:', $scope.documents.length);
                        } else {
                            console.warn('response.data não é um array:', response.data);
                            $scope.documents = [];
                        }
                    } else {
                        console.warn('Resposta inválida:', response);
                        $scope.documents = [];
                    }
                })
                .catch(function(error) {
                    console.error('Erro ao carregar documentos:', error);
                    if (error.data) {
                        console.error('Dados do erro:', error.data);
                    }
                    if (error.status) {
                        console.error('Status HTTP:', error.status);
                    }
                    $scope.documents = [];
                })
                .finally(function() {
                    $scope.loading = false;
                    console.log('Loading finalizado. Total documentos:', $scope.documents.length);
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

