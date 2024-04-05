from django.shortcuts import render

from rest_framework import viewsets

from .models import Apropriation
from .serializers import ApropriationSerializer

#class ProjectTypeViewSet(viewsets.ModelViewSet):
#    queryset = ProjectType.objects.all()
#   serializer_class = ProjectTypeSerializer

#class ProjectViewSet(viewsets.ModelViewSet):
#    queryset = Project.objects.all()
#    serializer_class = ProjectSerializer

class ApropriationViewSet(viewsets.ModelViewSet):
    queryset = Apropriation.objects.filter()
    serializer_class = ApropriationSerializer

def relatorio(request):
    apropriacoes = Apropriation.objects.filter(usuario=request.user)
    return render(request, 'report.html', {'apropriacoes': apropriacoes})