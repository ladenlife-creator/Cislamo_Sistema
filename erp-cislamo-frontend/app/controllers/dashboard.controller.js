angular.module('erpCislamoApp')
    .controller('DashboardController', ['$scope', '$location', 'AuthService', 'ApiService', 'DocumentService', 'EventService', function($scope, $location, AuthService, ApiService, DocumentService, EventService) {
        $scope.user = AuthService.getUser();
        $scope.stats = {};
        $scope.loading = true;
        
        $scope.loadStats = function() {
            $scope.loading = true;
            
            ApiService.get('/dashboard/stats')
                .then(function(response) {
                    console.log('Estatísticas do dashboard:', response.data);
                    
                    if (response && response.data) {
                        $scope.stats = response.data;
                        console.log('Utilizadores:', $scope.stats.users_count);
                        console.log('Documentos:', $scope.stats.documents_count);
                        console.log('Eventos:', $scope.stats.events_count);
                        console.log('Documentos recentes:', $scope.stats.recent_documents?.length || 0);
                        console.log('Próximos eventos:', $scope.stats.upcoming_events?.length || 0);
                    } else {
                        console.warn('Resposta inválida:', response);
                        $scope.stats = {};
                    }
                })
                .catch(function(error) {
                    console.error('Erro ao carregar estatísticas:', error);
                    if (error.data) {
                        console.error('Dados do erro:', error.data);
                    }
                    $scope.stats = {};
                })
                .finally(function() {
                    $scope.loading = false;
                });
        };
        
        // Função para visualizar documento (mesma lógica do documents.controller)
        $scope.viewDocument = function(doc) {
            console.log('Visualizando documento do dashboard:', doc);
            
            // Se já temos file_path nos dados do dashboard, usar diretamente
            if (doc.file_path && doc.file_path.trim() !== '') {
                openDocumentFile(doc);
                return;
            }
            
            // Se não tem file_path, buscar documento completo
            if (doc.id) {
                DocumentService.getById(doc.id)
                    .then(function(response) {
                        var fullDoc = response.data;
                        if (fullDoc && fullDoc.file_path && fullDoc.file_path.trim() !== '') {
                            openDocumentFile(fullDoc);
                        } else {
                            // Se não tem arquivo, navegar para documentos
                            $location.path('/documents');
                        }
                    })
                    .catch(function(error) {
                        console.warn('Não foi possível buscar documento completo, navegando para página de documentos');
                        $location.path('/documents');
                    });
            } else {
                // Se não tem ID, apenas navegar para documentos
                $location.path('/documents');
            }
        };
        
        // Função auxiliar para abrir arquivo do documento
        function openDocumentFile(doc) {
            var filePath = doc.file_path;
            var fileType = doc.file_type || '';
            var extension = filePath.split('.').pop().toLowerCase();
            var encodedPath = encodeURIComponent(filePath);
            
            if (extension === 'pdf' || fileType.includes('pdf')) {
                var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                window.open(fileUrl, '_blank');
                return;
            }
            
            if (extension === 'doc' || extension === 'docx' || fileType.includes('word') || fileType.includes('document')) {
                var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                var viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(fileUrl);
                window.open(viewerUrl, '_blank');
                return;
            }
            
            if (extension === 'xls' || extension === 'xlsx' || fileType.includes('excel') || fileType.includes('spreadsheet')) {
                var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                var viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(fileUrl);
                window.open(viewerUrl, '_blank');
                return;
            }
            
            if (extension === 'txt' || fileType.includes('text')) {
                var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                window.open(fileUrl, '_blank');
                return;
            }
            
            var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
            window.open(fileUrl, '_blank');
        }
        
        // Função para visualizar evento (navegar para página de eventos)
        $scope.viewEvent = function(event) {
            console.log('Visualizando evento do dashboard:', event);
            // Navegar para página de eventos (lá terá todas as funcionalidades)
            $location.path('/events');
        };
        
        $scope.logout = function() {
            AuthService.logout().finally(function() {
                $location.path('/login');
            });
        };
        
        $scope.goTo = function(path) {
            $location.path(path);
        };
        
        $scope.loadStats();
    }]);

