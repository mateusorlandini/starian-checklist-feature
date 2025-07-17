import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { RouterOutlet } from '@angular/router';
import { TodoService } from './service';
import { Todo } from './todo';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, CommonModule, FormsModule, HttpClientModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements OnInit {
  title = 'Todo List'
  todos: Todo[] = [];
  newTodo: Partial<Todo> = { title: '', completed: false };

  // Adicionado service ao contrutor
  constructor(private todoService: TodoService) { }

  ngOnInit(): void {
    this.todoService.getTodos().subscribe({
      next: (data: Todo[]) => {
        this.todos = data;
      },
      error: (erro) => {
        console.error('Erro ao carregar tarefas:', erro);
        this.todos = [
          { id: 1, title: 'Tarefa offline 1', completed: false },
          { id: 2, title: 'Tarefa offline 2', completed: true }
        ];
      }
    });
  }

  addTodo() {
    if (!(this.newTodo.title?.trim() ?? '')) return;

    const todoToSend = {
      title: this.newTodo.title,
      completed: false
    };

    this.todoService.addTodo(todoToSend).subscribe({
      next: (response) => {
        this.todos.push(response);
        this.newTodo = { title: '', completed: false };
      },
      error: (error) => {
        console.error('Erro ao adicionar tarefa:', error);
        const fakeTodo: Todo = {
          id: Math.floor(Math.random() * 1000000),
          title: this.newTodo.title ?? '',
          completed: false
        };
        this.todos.push(fakeTodo);
        this.newTodo = { title: '', completed: false };
      }
    });
  }

  removeTodo(id: number) {
    this.todoService.deleteTodo(id).subscribe({
      next: () => {
        this.filterTodo(id);
      },
      error: (erro) => {
        console.error('Erro ao remover tarefa:', erro);
        this.filterTodo(id);
      }
    });
  }

  filterTodo(id: number){
    this.todos = this.todos.filter(todo => todo.id !== id);
  }
}