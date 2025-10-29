import pandas as pd
import matplotlib.pyplot as plt
df = pd.read_csv("datos.csv")

df = df[df['edad'] > 25]

df_agrupado = df.groupby(['departamento'])

resultado = df_agrupado.agg({'salario': 'mean', 'ventas': 'sum'})

resultado = resultado.reset_index()
resultado.plot(kind='bar', x='departamento', y='salario')
plt.show()

resultado.to_csv("resultado.csv")

