from rest_framework import serializers
from .models import Apropriation
#class ProjectTypeSerializer(serializers.ModelSerializer):
#    class Meta:
#        model = ProjectType
#        fields = ('id', 'type')

#class ProjectSerializer(serializers.ModelSerializer):

#    class Meta:
#        model = Project
#        fields = ('id', 'cod_proj', 'name', 'length', 'rooms', 'project_cost', 'administrative_cost', 'visits','project_type')
class ApropriationSerializer(serializers.ModelSerializer):
    class Meta:
        model = Apropriation
        fields = '__all__'

    def create(self, validated_data):
        validated_data['usuario'] = self.context['request'].user
        return super().create(validated_data)