
using System;
using System.Text;
using System.Collections.Generic;


public class Parser {
	public const int _EOF = 0;
	public const int _ident = 1;
	public const int _number = 2;
	public const int _string = 3;
	public const int _ANALIZAR = 4;
	public const int _DATOS = 5;
	public const int _FILTRAR = 6;
	public const int _WHERE = 7;
	public const int _AGRUPAR = 8;
	public const int _POR = 9;
	public const int _CALCULAR = 10;
	public const int _GRAFICAR = 11;
	public const int _GUARDAR = 12;
	public const int _COMO = 13;
	public const int _PROMEDIO = 14;
	public const int _SUMA = 15;
	public const int _COUNT = 16;
	public const int _BARRAS = 17;
	public const int _LINEA = 18;
	public const int _DISPERSION = 19;
	public const int maxT = 32;

	const bool _T = true;
	const bool _x = false;
	const int minErrDist = 2;
	
	public Scanner scanner;
	public Errors  errors;

	public Token t;    // last recognized token
	public Token la;   // lookahead token
	int errDist = minErrDist;



private StringBuilder pythonCode;

/* ===== CARACTERES Y CONJUNTOS ===== */


	public Parser(Scanner scanner) {
		this.scanner = scanner;
		errors = new Errors();
	}

	void SynErr (int n) {
		if (errDist >= minErrDist) errors.SynErr(la.line, la.col, n);
		errDist = 0;
	}

	public void SemErr (string msg) {
		if (errDist >= minErrDist) errors.SemErr(t.line, t.col, msg);
		errDist = 0;
	}
	
	void Get () {
		for (;;) {
			t = la;
			la = scanner.Scan();
			if (la.kind <= maxT) { ++errDist; break; }

			la = t;
		}
	}
	
	void Expect (int n) {
		if (la.kind==n) Get(); else { SynErr(n); }
	}
	
	bool StartOf (int s) {
		return set[s, la.kind];
	}
	
	void ExpectWeak (int n, int follow) {
		if (la.kind == n) Get();
		else {
			SynErr(n);
			while (!StartOf(follow)) Get();
		}
	}


	bool WeakSeparator(int n, int syFol, int repFol) {
		int kind = la.kind;
		if (kind == n) {Get(); return true;}
		else if (StartOf(repFol)) {return false;}
		else {
			SynErr(n);
			while (!(set[syFol, kind] || set[repFol, kind] || set[0, kind])) {
				Get();
				kind = la.kind;
			}
			return StartOf(syFol);
		}
	}

	
	void LenguajeAnalisisDatos() {
		pythonCode = new StringBuilder();
		pythonCode.AppendLine("import pandas as pd");
		pythonCode.AppendLine("import matplotlib.pyplot as plt"); 
		pythonCode.AppendLine("print('=== INICIANDO ANÁLISIS DE DATOS ===')"); 
		while (StartOf(1)) {
			Comando();
			pythonCode.AppendLine(); 
		}
		pythonCode.AppendLine("print('=== ANÁLISIS COMPLETADO ===')");
		pythonCode.AppendLine("print('DataFrame original:', resultado.shape)");
		pythonCode.AppendLine("if 'resultado' in locals():");
		pythonCode.AppendLine("    print('Resultados calculados:')");
		pythonCode.AppendLine("    print(resultado)");
		pythonCode.AppendLine("else:");
		pythonCode.AppendLine("    print('No se calcularon resultados agrupados')");
		
		Console.WriteLine("=== CÓDIGO PYTHON GENERADO ===");
		Console.WriteLine(pythonCode.ToString());
		File.WriteAllText("output.py", pythonCode.ToString()); 
		
	}

	void Comando() {
		switch (la.kind) {
		case 4: {
			AnalizarDatos();
			break;
		}
		case 6: {
			FiltrarDatos();
			break;
		}
		case 8: {
			AgruparDatos();
			break;
		}
		case 10: {
			CalcularDatos();
			break;
		}
		case 11: {
			GraficarDatos();
			break;
		}
		case 12: {
			GuardarResultado();
			break;
		}
		default: SynErr(33); break;
		}
	}

	void AnalizarDatos() {
		string archivo = ""; 
		Expect(4);
		Expect(5);
		pythonCode.Append("resultado = pd.read_csv("); 
		Expect(3);
		archivo = t.val; 
		pythonCode.AppendLine(archivo + ")"); 
		// MOSTRAR INFORMACIÓN DEL DATASET
		pythonCode.AppendLine("print('Dataset cargado:', resultado.shape)");
		pythonCode.AppendLine("print('Columnas:', list(resultado.columns))");
		pythonCode.AppendLine("print('Primeras filas:')");
		pythonCode.AppendLine("print(resultado.head())"); 
	}

	void FiltrarDatos() {
		string expr = ""; 
		Expect(6);
		Expect(7);
		pythonCode.Append("resultado = resultado["); 
		Expression();
		pythonCode.AppendLine("]"); 
		// MOSTRAR FILTRO APLICADO
		pythonCode.AppendLine("print('Filtro aplicado. Nuevo shape:', resultado.shape)"); 
	}

	void AgruparDatos() {
		string columna = ""; 
		Expect(8);
		Expect(9);
		pythonCode.Append("resultado = resultado.groupby("); 
		Expect(1);
		columna = t.val; 
		pythonCode.AppendLine("['" + columna + "'])"); 
	}

	void CalcularDatos() {
		int count = 0; 
		Expect(10);
		pythonCode.Append("resultado = resultado.agg({"); 
		Funcion();
		count++; 
		while (la.kind == 25) {
			Get();
			pythonCode.Append(", "); 
			Funcion();
			count++; 
		}
		pythonCode.AppendLine("})"); 
	}

	void GraficarDatos() {
		Expect(11);
		Expect(28);
		Expect(29);
		if (la.kind == 17 || la.kind == 18) {
			GraficoNormal();
		} else if (la.kind == 19) {
			GraficoDispersion();
		} else SynErr(34);
	}

	void GuardarResultado() {
		string archivo = ""; 
		Expect(12);
		Expect(13);
		pythonCode.Append("resultado.to_csv("); 
		Expect(3);
		archivo = t.val; 
		pythonCode.AppendLine(archivo + ")"); 
	}

	void Expression() {
		string columna = "", operador = "", valor = ""; 
		Expect(1);
		columna = t.val; 
		pythonCode.Append("resultado['" + columna + "'] "); 
		if (la.kind == 20) {
			Get();
			operador = ">"; 
		} else if (la.kind == 21) {
			Get();
			operador = "<"; 
		} else if (la.kind == 22) {
			Get();
			operador = ">="; 
		} else if (la.kind == 23) {
			Get();
			operador = "<="; 
		} else if (la.kind == 24) {
			Get();
			operador = "=="; 
		} else SynErr(35);
		pythonCode.Append(operador + " "); 
		if (la.kind == 2) {
			Get();
			valor = t.val; pythonCode.Append(valor); 
		} else if (la.kind == 3) {
			Get();
			valor = t.val; pythonCode.Append(valor); 
		} else SynErr(36);
	}

	void Funcion() {
		string tipo = "", col = ""; 
		if (la.kind == 14) {
			Get();
			tipo = "mean"; 
		} else if (la.kind == 15) {
			Get();
			tipo = "sum"; 
		} else if (la.kind == 16) {
			Get();
			tipo = "count"; 
		} else SynErr(37);
		Expect(26);
		Expect(1);
		col = t.val; 
		pythonCode.Append("'" + col + "': '" + tipo + "'"); 
		Expect(27);
	}

	void GraficoNormal() {
		string tipoGrafico = "", ejeX = "", ejeY = ""; 
		if (la.kind == 17) {
			Get();
			tipoGrafico = "bar"; 
		} else if (la.kind == 18) {
			Get();
			tipoGrafico = "line"; 
		} else SynErr(38);
		pythonCode.AppendLine("resultado = resultado.reset_index()");
		pythonCode.Append("resultado.plot(kind='" + tipoGrafico + "'"); 
		
		if (la.kind == 30) {
			Get();
			Expect(29);
			Expect(1);
			ejeX = t.val; 
			pythonCode.Append(", x='" + ejeX + "'"); 
		}
		if (la.kind == 31) {
			Get();
			Expect(29);
			Expect(1);
			ejeY = t.val; 
			pythonCode.Append(", y='" + ejeY + "'"); 
		}
		pythonCode.AppendLine(")");
		pythonCode.AppendLine("plt.title('Resultados del Análisis')");
		pythonCode.AppendLine("plt.tight_layout()");
		pythonCode.AppendLine("plt.savefig('grafico.png')");
		pythonCode.AppendLine("print('Gráfico guardado como grafico.png')");
		
	}

	void GraficoDispersion() {
		string ejeX = "", ejeY = ""; 
		Expect(19);
		pythonCode.AppendLine("resultado = resultado.reset_index()");
		pythonCode.AppendLine("# Gráfico de dispersión");
		pythonCode.Append("plt.scatter(x=resultado['");
		
		if (la.kind == 30) {
			Get();
			Expect(29);
			Expect(1);
			ejeX = t.val; 
			pythonCode.Append(ejeX + "'], y=resultado['"); 
		}
		if (la.kind == 31) {
			Get();
			Expect(29);
			Expect(1);
			ejeY = t.val; 
			pythonCode.Append(ejeY + "'])"); 
		}
		pythonCode.AppendLine("plt.title('Gráfico de Dispersión')");
		pythonCode.AppendLine("plt.xlabel('" + ejeX + "')");
		pythonCode.AppendLine("plt.ylabel('" + ejeY + "')");
		pythonCode.AppendLine("plt.tight_layout()");
		pythonCode.AppendLine("plt.savefig('grafico.png')");
		pythonCode.AppendLine("print('Gráfico guardado como grafico.png')");
		
	}



	public void Parse() {
		la = new Token();
		la.val = "";		
		Get();
		LenguajeAnalisisDatos();
		Expect(0);

	}
	
	static readonly bool[,] set = {
		{_T,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x},
		{_x,_x,_x,_x, _T,_x,_T,_x, _T,_x,_T,_T, _T,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x,_x,_x, _x,_x}

	};
} // end Parser


public class Errors {
	public int count = 0;                                    // number of errors detected
	public System.IO.TextWriter errorStream = Console.Out;   // error messages go to this stream
	public string errMsgFormat = "-- line {0} col {1}: {2}"; // 0=line, 1=column, 2=text

	public virtual void SynErr (int line, int col, int n) {
		string s;
		switch (n) {
			case 0: s = "EOF expected"; break;
			case 1: s = "ident expected"; break;
			case 2: s = "number expected"; break;
			case 3: s = "string expected"; break;
			case 4: s = "ANALIZAR expected"; break;
			case 5: s = "DATOS expected"; break;
			case 6: s = "FILTRAR expected"; break;
			case 7: s = "WHERE expected"; break;
			case 8: s = "AGRUPAR expected"; break;
			case 9: s = "POR expected"; break;
			case 10: s = "CALCULAR expected"; break;
			case 11: s = "GRAFICAR expected"; break;
			case 12: s = "GUARDAR expected"; break;
			case 13: s = "COMO expected"; break;
			case 14: s = "PROMEDIO expected"; break;
			case 15: s = "SUMA expected"; break;
			case 16: s = "COUNT expected"; break;
			case 17: s = "BARRAS expected"; break;
			case 18: s = "LINEA expected"; break;
			case 19: s = "DISPERSION expected"; break;
			case 20: s = "\">\" expected"; break;
			case 21: s = "\"<\" expected"; break;
			case 22: s = "\">=\" expected"; break;
			case 23: s = "\"<=\" expected"; break;
			case 24: s = "\"==\" expected"; break;
			case 25: s = "\",\" expected"; break;
			case 26: s = "\"(\" expected"; break;
			case 27: s = "\")\" expected"; break;
			case 28: s = "\"tipo\" expected"; break;
			case 29: s = "\"=\" expected"; break;
			case 30: s = "\"eje_x\" expected"; break;
			case 31: s = "\"eje_y\" expected"; break;
			case 32: s = "??? expected"; break;
			case 33: s = "invalid Comando"; break;
			case 34: s = "invalid GraficarDatos"; break;
			case 35: s = "invalid Expression"; break;
			case 36: s = "invalid Expression"; break;
			case 37: s = "invalid Funcion"; break;
			case 38: s = "invalid GraficoNormal"; break;

			default: s = "error " + n; break;
		}
		errorStream.WriteLine(errMsgFormat, line, col, s);
		count++;
	}

	public virtual void SemErr (int line, int col, string s) {
		errorStream.WriteLine(errMsgFormat, line, col, s);
		count++;
	}
	
	public virtual void SemErr (string s) {
		errorStream.WriteLine(s);
		count++;
	}
	
	public virtual void Warning (int line, int col, string s) {
		errorStream.WriteLine(errMsgFormat, line, col, s);
	}
	
	public virtual void Warning(string s) {
		errorStream.WriteLine(s);
	}
} // Errors


public class FatalError: Exception {
	public FatalError(string m): base(m) {}
}
