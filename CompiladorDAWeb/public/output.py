import pandas as pd
import matplotlib.pyplot as plt
print('=== INICIANDO ANÁLISIS DE DATOS ===')
resultado = pd.read_csv("datos.csv")
print('Dataset cargado:', resultado.shape)
print('Columnas:', list(resultado.columns))
print('Primeras filas:')
print(resultado.head())

resultado = resultado[resultado['edad'] > 25]
print('Filtro aplicado. Nuevo shape:', resultado.shape)

resultado = resultado.groupby(['departamento'])

resultado = resultado.agg({'salario': 'mean', 'ventas': 'sum'})

resultado = resultado.reset_index()
resultado.plot(kind='bar', x='departamento', y='salario')
plt.title('Resultados del Análisis')
plt.xlabel('departamento')
plt.ylabel('salario')
plt.tight_layout()
plt.savefig('grafico.png')
print('Gráfico guardado como grafico.png')

resultado.to_csv("resultado.csv")

# Guardar resultado automáticamente
resultado.to_csv('resultado.csv')
print('Resultados guardados automáticamente en resultado.csv')
print('=== ANÁLISIS COMPLETADO ===')
print('DataFrame original:', resultado.shape)
if 'resultado' in locals():
    print('Resultados calculados:')
    print(resultado)
else:
    print('No se calcularon resultados agrupados')
