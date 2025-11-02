angular.module('erpCislamoApp')
    .controller('DocumentsController', ['$scope', '$location', 'AuthService', 'DocumentService', function($scope, $location, AuthService, DocumentService) {
        $scope.documents = [];
        $scope.allDocuments = []; // Todos os documentos (sem filtro)
        $scope.loading = true;
        $scope.showForm = false;
        $scope.showFilters = false; // Controla visibilidade dos filtros
        $scope.showDocumentModal = false;
        $scope.selectedDocument = null;
        $scope.currentUser = AuthService.getUser();
        
        // Filtros
        $scope.filters = {
            search: '',
            category: '',
            dateFrom: '',
            dateTo: ''
        };
        
        $scope.categoryOptions = [
            { value: '', label: 'Todas as Categorias' },
            { value: 'Relatório', label: 'Relatório' },
            { value: 'Manual', label: 'Manual' },
            { value: 'Política', label: 'Política' },
            { value: 'Contrato', label: 'Contrato' },
            { value: 'Plano', label: 'Plano' },
            { value: 'Orçamento', label: 'Orçamento' },
            { value: 'Outro', label: 'Outro' }
        ];
        
        $scope.newDocument = {
            title: '',
            description: '',
            category: '',
            file: null
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
                            $scope.allDocuments = response.data;
                            $scope.applyFilters(); // Aplicar filtros após carregar
                            console.log('Documentos atribuídos:', $scope.allDocuments.length);
                        } else {
                            console.warn('response.data não é um array:', response.data);
                            $scope.allDocuments = [];
                            $scope.documents = [];
                        }
                    } else {
                        console.warn('Resposta inválida:', response);
                        $scope.allDocuments = [];
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
                    $scope.allDocuments = [];
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
        
        $scope.toggleFilters = function() {
            $scope.showFilters = !$scope.showFilters;
        };
        
        $scope.createDocument = function() {
            if ($scope.newDocument.title) {
                // Preparar dados do documento
                var documentData = {
                    title: $scope.newDocument.title,
                    description: $scope.newDocument.description || '',
                    category: $scope.newDocument.category || 'Outro'
                };
                
                // Se houver arquivo selecionado, adicionar informações do arquivo
                if ($scope.newDocument.file) {
                    var file = $scope.newDocument.file;
                    // Extrair extensão do arquivo
                    var extension = file.name.split('.').pop().toLowerCase();
                    var mimeTypes = {
                        'pdf': 'application/pdf',
                        'doc': 'application/msword',
                        'docx': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'xls': 'application/vnd.ms-excel',
                        'xlsx': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'txt': 'text/plain'
                    };
                    
                    documentData.file_type = mimeTypes[extension] || file.type || 'application/octet-stream';
                    documentData.file_size = file.size || 0;
                    // Para upload completo, seria necessário usar FormData e endpoint específico
                    // Por enquanto, apenas armazenamos as informações básicas do arquivo
                    documentData.file_path = '/documents/' + file.name;
                }
                
                DocumentService.create(documentData)
                    .then(function(response) {
                        console.log('Resposta completa do servidor:', response);
                        console.log('response.data:', response.data);
                        
                        // Verificar se a resposta tem o formato esperado
                        var newDoc = response.data;
                        if (newDoc && newDoc.id) {
                            console.log('Documento criado com ID:', newDoc.id);
                            $scope.allDocuments.unshift(newDoc);
                            $scope.applyFilters(); // Reaplicar filtros após criar
                            $scope.toggleForm();
                            alert('Documento criado com sucesso!');
                        } else {
                            console.warn('Resposta inesperada do servidor:', response);
                            // Recarregar a lista completa mesmo assim
                            $scope.loadDocuments();
                            $scope.toggleForm();
                            alert('Documento pode ter sido criado. Recarregando lista...');
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro ao criar documento:', error);
                        if (error.data) {
                            console.error('Dados do erro:', error.data);
                        }
                        if (error.status) {
                            console.error('Status HTTP:', error.status);
                        }
                        alert('Erro ao criar documento: ' + (error.data?.message || error.statusText || 'Erro desconhecido. Verifique o console.'));
                    });
            }
        };
        
        $scope.viewDocument = function(doc, event) {
            if (event) {
                event.stopPropagation();
            }
            
            console.log('Visualizando documento:', doc);
            
            // Se o documento tem um arquivo associado (file_path)
            if (doc.file_path && doc.file_path.trim() !== '') {
                // Determinar tipo de arquivo pela extensão ou file_type
                var filePath = doc.file_path;
                var fileType = doc.file_type || '';
                var extension = filePath.split('.').pop().toLowerCase();
                
                // Codificar o caminho do arquivo para a URL
                var encodedPath = encodeURIComponent(filePath);
                
                // Para PDFs, abrir diretamente no navegador usando a rota de arquivo
                if (extension === 'pdf' || fileType.includes('pdf')) {
                    var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                    window.open(fileUrl, '_blank');
                    return;
                }
                
                // Para documentos Word/DOC/DOCX, usar Office Online Viewer
                if (extension === 'doc' || extension === 'docx' || fileType.includes('word') || fileType.includes('document')) {
                    var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                    // Usar Office Online Viewer
                    var viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(fileUrl);
                    window.open(viewerUrl, '_blank');
                    return;
                }
                
                // Para Excel/XLS/XLSX
                if (extension === 'xls' || extension === 'xlsx' || fileType.includes('excel') || fileType.includes('spreadsheet')) {
                    var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                    var viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(fileUrl);
                    window.open(viewerUrl, '_blank');
                    return;
                }
                
                // Para arquivos de texto
                if (extension === 'txt' || fileType.includes('text')) {
                    var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                    window.open(fileUrl, '_blank');
                    return;
                }
                
                // Para outros tipos, tentar abrir diretamente
                var fileUrl = 'http://localhost:8000/api/documents/file/' + encodedPath;
                window.open(fileUrl, '_blank');
            } else {
                // Se não tem arquivo, mostrar modal com detalhes
                $scope.selectedDocument = doc;
                $scope.showDocumentModal = true;
            }
        };
        
        $scope.closeDocumentModal = function() {
            $scope.showDocumentModal = false;
            $scope.selectedDocument = null;
        };
        
        // Função para aplicar filtros
        $scope.applyFilters = function() {
            var filtered = $scope.allDocuments.filter(function(doc) {
                // Filtro de busca (título ou descrição)
                if ($scope.filters.search) {
                    var search = $scope.filters.search.toLowerCase();
                    var titleMatch = doc.title && doc.title.toLowerCase().indexOf(search) !== -1;
                    var descMatch = doc.description && doc.description.toLowerCase().indexOf(search) !== -1;
                    if (!titleMatch && !descMatch) {
                        return false;
                    }
                }
                
                // Filtro por categoria
                if ($scope.filters.category && doc.category !== $scope.filters.category) {
                    return false;
                }
                
                // Filtro por data de criação (a partir de)
                if ($scope.filters.dateFrom && doc.created_at) {
                    var dateFrom = new Date($scope.filters.dateFrom);
                    var docDate = new Date(doc.created_at);
                    if (docDate < dateFrom) {
                        return false;
                    }
                }
                
                // Filtro por data de criação (até)
                if ($scope.filters.dateTo && doc.created_at) {
                    var dateTo = new Date($scope.filters.dateTo);
                    dateTo.setHours(23, 59, 59); // Incluir o dia inteiro
                    var docDate = new Date(doc.created_at);
                    if (docDate > dateTo) {
                        return false;
                    }
                }
                
                return true;
            });
            
            $scope.documents = filtered;
        };
        
        // Função para limpar filtros
        $scope.clearFilters = function() {
            $scope.filters = {
                search: '',
                category: '',
                dateFrom: '',
                dateTo: ''
            };
            $scope.applyFilters();
        };
        
        $scope.deleteDocument = function(docId, event) {
            if (event) {
                event.stopPropagation();
            }
            if (confirm('Tem certeza que deseja remover este documento?')) {
                DocumentService.delete(docId)
                    .then(function() {
                        $scope.allDocuments = $scope.allDocuments.filter(function(d) { return d.id !== docId; });
                        $scope.applyFilters(); // Reaplicar filtros após remover
                        alert('Documento removido com sucesso!');
                    })
                    .catch(function(error) {
                        alert('Erro ao remover documento.');
                    });
            }
        };
        
        $scope.onFileSelect = function(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                $scope.$apply(function() {
                    $scope.newDocument.file = file;
                    $scope.newDocument.fileName = file.name;
                    $scope.newDocument.fileSize = file.size;
                });
            }
        };
        
        $scope.resetForm = function() {
            $scope.newDocument = {
                title: '',
                description: '',
                category: '',
                file: null
            };
            // Limpar input de arquivo também
            var fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.value = '';
            }
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

